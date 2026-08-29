<?php

namespace App\Support;

/**
 * Minimal Chrome DevTools Protocol client for Page.printToPDF.
 *
 * Headless Chrome's --print-to-pdf CLI often ignores @page size and defaults to
 * A4. CDP lets us pass an explicit paper size so receipts stay A5 landscape.
 */
class ChromeDevtools
{
    private $socket;

    private int $messageId = 0;

    public function __construct(string $webSocketDebuggerUrl)
    {
        $parts = parse_url($webSocketDebuggerUrl);
        if (!is_array($parts) || empty($parts['host']) || empty($parts['path'])) {
            throw new \RuntimeException('Invalid Chrome DevTools WebSocket URL.');
        }

        $host = $parts['host'];
        $port = $parts['port'] ?? 80;
        $path = $parts['path'] . (isset($parts['query']) ? '?' . $parts['query'] : '');

        $socket = @stream_socket_client(
            'tcp://' . $host . ':' . $port,
            $errno,
            $errstr,
            10
        );
        if ($socket === false) {
            throw new \RuntimeException('Chrome DevTools socket failed: ' . $errstr);
        }

        stream_set_timeout($socket, 30);

        $key = base64_encode(random_bytes(16));
        $handshake = "GET {$path} HTTP/1.1\r\n"
            . "Host: {$host}:{$port}\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Key: {$key}\r\n"
            . "Sec-WebSocket-Version: 13\r\n\r\n";

        fwrite($socket, $handshake);

        $response = '';
        while (($line = fgets($socket)) !== false) {
            $response .= $line;
            if ($line === "\r\n") {
                break;
            }
        }

        if (!str_contains($response, '101')) {
            fclose($socket);
            throw new \RuntimeException('Chrome DevTools WebSocket handshake failed.');
        }

        $this->socket = $socket;
    }

    /** @param array<string, mixed> $params */
    public function call(string $method, array $params = []): array
    {
        $id = ++$this->messageId;
        $this->writeFrame(json_encode([
            'id' => $id,
            'method' => $method,
            'params' => $params,
        ], JSON_THROW_ON_ERROR));

        while (true) {
            $payload = $this->readFrame();
            if ($payload === '') {
                continue;
            }

            $message = json_decode($payload, true);
            if (!is_array($message)) {
                continue;
            }

            if (($message['id'] ?? null) === $id) {
                return $message;
            }
        }
    }

    public function close(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }

    private function writeFrame(string $payload): void
    {
        $length = strlen($payload);
        $frame = chr(0x81);

        if ($length <= 125) {
            $frame .= chr(0x80 | $length);
        } elseif ($length <= 65535) {
            $frame .= chr(0x80 | 126) . pack('n', $length);
        } else {
            $frame .= chr(0x80 | 127) . pack('J', $length);
        }

        $mask = random_bytes(4);
        $frame .= $mask;

        for ($i = 0; $i < $length; $i++) {
            $frame .= $payload[$i] ^ $mask[$i % 4];
        }

        fwrite($this->socket, $frame);
    }

    private function readFrame(): string
    {
        $header = fread($this->socket, 2);
        if ($header === false || strlen($header) < 2) {
            return '';
        }

        $opcode = ord($header[0]) & 0x0F;
        $masked = (ord($header[1]) & 0x80) !== 0;
        $length = ord($header[1]) & 0x7F;

        if ($length === 126) {
            $extended = fread($this->socket, 2);
            $length = unpack('n', $extended ?: "\0\0")[1];
        } elseif ($length === 127) {
            $extended = fread($this->socket, 8);
            $length = unpack('J', $extended ?: str_repeat("\0", 8))[1];
        }

        $mask = '';
        if ($masked) {
            $mask = fread($this->socket, 4) ?: '';
        }

        $payload = '';
        $remaining = $length;
        while ($remaining > 0) {
            $chunk = fread($this->socket, $remaining);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $payload .= $chunk;
            $remaining -= strlen($chunk);
        }

        if ($masked && $mask !== '') {
            $unmasked = '';
            for ($i = 0, $len = strlen($payload); $i < $len; $i++) {
                $unmasked .= $payload[$i] ^ $mask[$i % 4];
            }
            $payload = $unmasked;
        }

        if ($opcode === 0x08) {
            return '';
        }

        if ($opcode === 0x09) {
            $this->writeFrame('');

            return $this->readFrame();
        }

        return $payload;
    }
}
