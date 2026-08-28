<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\File;

class ChatAssistantService
{
    public function __construct(private AiChatService $ai)
    {
    }

    /** @return array<string, mixed> */
    public function reply(string $message, ?string $locale = null): array
    {
        $message = trim($message);
        $ar = $locale === 'ar' || ($locale !== 'en' && $this->looksArabic($message));

        if ($message !== '' && $this->ai->enabled()) {
            $products = $this->productsForAiContext($message);
            $aiReply = $this->ai->reply($message, $ar, $products);
            if ($aiReply !== null) {
                return $aiReply;
            }
        }

        return $this->ruleBasedReply($message, $ar);
    }

    /** @return array<string, mixed> */
    private function ruleBasedReply(string $message, bool $ar): array
    {
        $company = $this->companyInfo();
        $phone = $company['contact']['phone'] ?? '+971 56 256 6232';
        $whatsapp = $company['contact']['whatsapp'] ?? '971562566232';
        $email = $company['contact']['email'] ?? 'info@smartflow.ae';
        $hours = $ar
            ? ($company['workingHours']['ar'] ?? 'الاثنين–السبت 9–6')
            : ($company['workingHours']['en'] ?? 'Mon–Sat 9 AM–6 PM');

        $text = mb_strtolower($message);
        $actions = [];

        if ($text === '' || $this->isGreeting($text)) {
            return $this->pack($ar
                ? "مرحباً بك في سمارت فلو 👋\n\nنحن متخصصون في البيت الذكي، أنظمة الأمان، ماكينات البوابات، والكاميرات في الإمارات.\n\nاختر موضوعاً أو اكتب سؤالك، أو تواصل معنا مباشرة على واتساب."
                : "Welcome to SmartFlow 👋\n\nWe specialize in smart home automation, security, gate motors, and CCTV across the UAE.\n\nPick a topic below or type your question — or reach us on WhatsApp.",
                $this->defaultActions($ar));
        }

        if ($this->matches($text, ['whatsapp', 'واتس', 'واتساب', 'chat'])) {
            $actions[] = $this->whatsappAction($ar, $whatsapp, $ar ? 'فتح واتساب' : 'Open WhatsApp');
            return $this->pack($ar
                ? "يمكنك التواصل مع فريقنا فوراً على واتساب {$phone} خلال {$hours}."
                : "Reach our team on WhatsApp at {$phone}. Hours: {$hours}.",
                $actions);
        }

        if ($this->matches($text, [
            'contact', 'support', 'help', 'reach', 'call', 'phone', 'touch', 'assist', 'customer service',
            'تواصل', 'اتصل', 'اتصال', 'دعم', 'فريق', 'مساعدة', 'رقم', 'هاتف', 'تواصل مع', 'كيف اتواصل', 'كيف أتواصل',
            'اتواصل', 'أتواصل', 'راسل', 'راسلنا', 'اتصل ب', 'اتصل بنا', 'خدمة العملاء',
        ])) {
            return $this->pack($ar
                ? "للتواصل مع فريق الدعم:\n\n📱 واتساب (الأسرع): {$phone}\n📧 البريد: {$email}\n🕐 {$hours}\n\nاضغط «واتساب» للمحادثة المباشرة مع موظف."
                : "Contact our support team:\n\n📱 WhatsApp (fastest): {$phone}\n📧 Email: {$email}\n🕐 {$hours}\n\nTap WhatsApp to chat with our team.",
                [
                    $this->whatsappAction($ar, $whatsapp, $ar ? 'واتساب — فريق الدعم' : 'WhatsApp — Support'),
                    ['label' => $ar ? 'البريد الإلكتروني' : 'Email', 'href' => 'mailto:' . $email],
                ]);
        }

        if ($this->matches($text, ['price', 'cost', 'quote', 'سعر', 'أسعار', 'تكلف', 'عرض', 'quotation'])) {
            return $this->pack($ar
                ? "الأسعار تعتمد على نوع النظام، الموقع، وعدد الأجهزة.\n\nلعرض سعر دقيق: أرسل لنا مخطط المشروع عبر «دراسة مشروع» أو راسلنا على واتساب."
                : "Pricing depends on the system, site, and scope.\n\nFor an accurate quote, use our Project Study form or message us on WhatsApp.",
                array_merge($this->defaultActions($ar), [$this->whatsappAction($ar, $whatsapp)]));
        }

        if ($this->isSmartHomeQuestion($text)) {
            $actions[] = ['label' => $ar ? 'ابدأ دراسة مشروع' : 'Start project study', 'href' => '/project-study'];
            return $this->pack($ar
                ? "لتحويل بيتك إلى بيت ذكي (Smart Home) ننصحك بالخطوات التالية:\n\n1️⃣ حدّد احتياجاتك: إضاءة، مكيف، ستائر، كاميرات، إنتركم، شبكة…\n2️⃣ املأ نموذج «دراسة مشروع» — فريقنا يزور الموقع ويُعد خطة مناسبة لمساحتك.\n3️⃣ نركّب الأنظمة ونربطها بتطبيق تحكم واحد من جوالك.\n\nابدأ الآن من «دراسة مشروع» أو راسلنا على واتساب {$phone}."
                : "To make your home smart, we recommend:\n\n1️⃣ Define your needs: lighting, AC, curtains, CCTV, intercom, networking…\n2️⃣ Fill our Project Study form — our team visits and plans the right setup.\n3️⃣ We install and connect everything to one app on your phone.\n\nStart with Project Study or WhatsApp us at {$phone}.",
                array_merge([$actions[0]], [$this->whatsappAction($ar, $whatsapp)]));
        }

        if ($this->matches($text, ['gate', 'door', 'motor', 'بواب', 'باب', 'ماكين', 'منزلق', 'swing'])) {
            $actions[] = ['label' => $ar ? 'دراسة ماكينة باب' : 'Gate machine study', 'href' => '/gate-machine-study'];
            return $this->pack($ar
                ? "نركّب ونصيانة ماكينات البوابات (منزلقة، سوينغ، كراج).\n\nلموقع خارجي أو باب كبير، استخدم نموذج «دراسة ماكينة باب» — نحتاج الوزن والأبعاد والموقع."
                : "We supply and install gate motors (sliding, swing, garage).\n\nFor external sites, use our Gate Machine Study form with door weight, size, and location.",
                $actions);
        }

        if ($this->matches($text, ['study', 'project', 'villa', 'home', 'smart', 'دراس', 'مشروع', 'فيلا', 'ذكي', 'ذكية', 'منزل', 'سمارت', 'بيت', 'بيتي', 'أتمت', 'automation', 'iot'])) {
            $actions[] = ['label' => $ar ? 'دراسة مشروع' : 'Project study', 'href' => '/project-study'];
            return $this->pack($ar
                ? "«دراسة مشروع» للمنازل الذكية: كاميرات، إنتركم، إضاءة، مكيف، ستائر، شبكات…\n\nاملأ الاستبيان وسنحدد موعد زيارة إن لزم."
                : "Project Study is for smart home planning: CCTV, intercom, lighting, AC, curtains, networking…\n\nFill the form and we’ll schedule a visit if needed.",
                $actions);
        }

        if ($this->matches($text, ['camera', 'cctv', 'security', 'alarm', 'كامير', 'مراقب', 'امان', 'أمان', 'انذار'])) {
            return $this->pack($ar
                ? "نقدّم تركيب كاميرات المراقبة، أنظمة إنذار، وتحكم أمني متكامل للمنازل والشركات في الإمارات."
                : "We install CCTV, alarm systems, and integrated security for homes and businesses in the UAE.",
                $this->defaultActions($ar));
        }

        if ($this->matches($text, ['hour', 'time', 'open', 'when', 'موعد', 'وقت', 'ساعات', 'دوام', 'working'])) {
            return $this->pack($ar
                ? "ساعات العمل: {$hours}\n\nخارج الدوام راسلنا على واتساب وسنرد في أقرب وقت."
                : "Working hours: {$hours}\n\nOutside hours, message us on WhatsApp and we’ll reply ASAP.",
                [$this->whatsappAction($ar, $whatsapp)]);
        }

        if ($this->matches($text, ['where', 'location', 'address', 'uae', 'dubai', 'موقع', 'عنوان', 'دبي', 'امارات', 'إمارات'])) {
            $loc = $ar
                ? ($company['contact']['address']['ar'] ?? 'الإمارات')
                : ($company['contact']['address']['en'] ?? 'UAE');
            return $this->pack($ar
                ? "نخدم {$loc} والإمارات.\n\nللزيارة الميدانية: دراسة مشروع أو واتساب {$phone}."
                : "We serve {$loc} and the wider UAE.\n\nFor site visits: Project Study or WhatsApp {$phone}.",
                $this->defaultActions($ar));
        }

        if ($this->matches($text, ['email', 'mail', 'ايميل', 'إيميل', 'بريد'])) {
            return $this->pack($ar
                ? "البريد: {$email}\n\nللرد السريع نفضّل واتساب {$phone}."
                : "Email: {$email}\n\nFor faster replies, WhatsApp {$phone} works best.",
                [$this->whatsappAction($ar, $whatsapp)]);
        }

        if ($this->matches($text, ['service', 'services', 'خدم', 'خدمات', 'ماذا تقدم', 'what do you'])) {
            return $this->pack($ar
                ? "خدماتنا:\n• البيت الذكي (إضاءة، مكيف، ستائر، شبكات)\n• كاميرات ومراقبة\n• ماكينات البوابات\n• أنظمة إنذار وأمان\n• دراسة مشروع ميدانية\n\nاختر «دراسة مشروع» أو «ماكينة باب» أو اسأل عن منتج محدد."
                : "Our services:\n• Smart home (lighting, AC, curtains, networking)\n• CCTV & surveillance\n• Gate motors\n• Alarm & security systems\n• On-site project study\n\nTry Project Study, Gate Machine Study, or ask about a product.",
                $this->defaultActions($ar));
        }

        if ($this->matches($text, ['product', 'offer', 'catalog', 'منتج', 'منتجات', 'عروض'])) {
            $actions[] = ['label' => $ar ? 'المنتجات' : 'Products', 'href' => '/products'];
            return $this->pack($ar
                ? "تصفّح منتجاتنا وعروضنا على الموقع. للاستفسار عن منتج محدّد، أرسل اسمه على واتساب."
                : "Browse our products and offers on the site. For a specific item, send its name on WhatsApp.",
                $actions);
        }

        if ($this->matches($text, ['human', 'agent', 'person', 'موظف', 'شخص', 'بشر', 'مندوب'])) {
            return $this->pack($ar
                ? "حاضر — سأحوّلك لفريقنا على واتساب للمتابعة مع موظف."
                : "Sure — I'll connect you to our team on WhatsApp to speak with a person.",
                [$this->whatsappAction($ar, $whatsapp, $ar ? 'تحدث مع فريقنا' : 'Talk to our team')]);
        }

        $productReply = $this->tryProductSearch($message, $ar, $whatsapp);
        if ($productReply !== null) {
            return $productReply;
        }

        if ($this->matches($text, ['كيف', 'how', 'why', 'لماذا', 'متى', 'when', 'what', 'ماذا', 'هل'])) {
            return $this->pack($ar
                ? "سؤال جيد 👍\n\nيمكننا مساعدتك في:\n• تحويل بيتك لـ Smart Home → «دراسة مشروع»\n• ماكينات البوابات → «ماكينة باب»\n• منتجات محددة → اكتب اسم المنتج\n• التواصل مع فريقنا → واتساب {$phone}\n\nاكتب سؤالك باختصار أو اختر زراً أدناه."
                : "Good question 👍\n\nWe can help with:\n• Smart home setup → Project Study\n• Gate motors → Gate Machine Study\n• Specific products → type the product name\n• Talk to our team → WhatsApp {$phone}\n\nRephrase briefly or pick a button below.",
                $this->defaultActions($ar));
        }

        return $this->pack($ar
            ? "شكراً لسؤالك. لم أفهم بالضبط — جرّب:\n• دراسة مشروع\n• ماكينة باب\n• واتساب\n\nأو اكتب سؤالك بشكل أوضح."
            : "Thanks for your message. Try:\n• Project study\n• Gate machine\n• WhatsApp\n\nOr rephrase your question.",
            $this->defaultActions($ar));
    }

    /** @param list<string> $needles */
    private function matches(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, mb_strtolower($needle)) !== false) {
                return true;
            }
        }

        return false;
    }

    private function looksArabic(string $text): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }

    private function isGreeting(string $text): bool
    {
        if (preg_match('/^(hi|hello|hey)(\s|!|\?|$)/u', $text)) {
            return true;
        }

        return (bool) preg_match('/^(مرحب|السلام|اهلا|أهلا|هاي|مساء|صباح)/u', $text);
    }

    private function isSmartHomeQuestion(string $text): bool
    {
        $homeWords = ['بيت', 'بيتي', 'منزل', 'منزلي', 'فيلا', 'فيلتي', 'home', 'villa', 'house', 'شقة', 'شقتي', 'apartment'];
        $smartWords = ['سمارت', 'smart', 'ذكي', 'ذكية', 'أتمت', 'automation', 'iot', 'تحكم ذك', 'home automation', 'smart home'];

        $hasHome = $this->matches($text, $homeWords);
        $hasSmart = $this->matches($text, $smartWords);

        if ($hasHome && $hasSmart) {
            return true;
        }

        if ($this->matches($text, ['بيت ذك', 'منزل ذك', 'make my home', 'make home smart', 'turn my home'])) {
            return true;
        }

        return $this->matches($text, ['كيف', 'how', 'جعل', 'تحويل', 'أريد', 'want', 'need']) && ($hasHome || $hasSmart);
    }

    /** @return array<string, mixed> */
    private function companyInfo(): array
    {
        $path = public_path('company-info.json');
        if (!File::exists($path)) {
            return [];
        }
        $data = json_decode(File::get($path), true);

        return is_array($data) ? $data : [];
    }

    /** @param list<array<string, string>> $actions */
    /** @return array<string, mixed> */
    private function pack(string $reply, array $actions = []): array
    {
        return [
            'reply' => $reply,
            'actions' => array_values($actions),
        ];
    }

    /** @return list<array<string, string>> */
    private function defaultActions(bool $ar): array
    {
        return [
            ['label' => $ar ? 'دراسة مشروع' : 'Project study', 'href' => '/project-study'],
            ['label' => $ar ? 'ماكينة باب' : 'Gate machine', 'href' => '/gate-machine-study'],
            ['label' => $ar ? 'واتساب' : 'WhatsApp', 'href' => $this->whatsappHref($this->companyInfo()['contact']['whatsapp'] ?? '971562566232')],
        ];
    }

    /** @return array{label: string, href: string} */
    private function whatsappAction(bool $ar, string $number, ?string $label = null): array
    {
        return [
            'label' => $label ?? ($ar ? 'واتساب' : 'WhatsApp'),
            'href' => $this->whatsappHref($number),
        ];
    }

    private function whatsappHref(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number) ?: '971562566232';
        $msg = urlencode('مرحباً، أتواصل معكم من موقع SmartFlow');

        return "https://wa.me/{$digits}?text={$msg}";
    }

    /** @return array<string, mixed>|null */
    private function tryProductSearch(string $message, bool $ar, string $whatsapp): ?array
    {
        $query = $this->extractSearchQuery($message);
        if (mb_strlen($query) < 3) {
            return null;
        }

        $products = Product::visibleToPublic()
            ->where(function ($q) use ($query) {
                $like = '%' . $query . '%';
                $q->where('name', 'like', $like)
                    ->orWhere('name_ar', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('description_ar', 'like', $like);
            })
            ->orderBy('name_ar')
            ->limit(5)
            ->get(['id', 'name', 'name_ar', 'brand', 'price', 'price_number']);

        if ($products->isEmpty()) {
            return null;
        }

        $lines = [];
        $actions = [];
        foreach ($products as $product) {
            $label = trim($product->name_ar ?: $product->name ?: '');
            if ($product->brand) {
                $label = $product->brand . ($label !== '' ? ' — ' . $label : '');
            }
            $price = $product->price ?: ($product->price_number ? number_format((float) $product->price_number, 2) . ' AED' : '');
            $line = '• ' . ($label ?: ($ar ? 'منتج' : 'Product') . ' #' . $product->id);
            if ($price !== '') {
                $line .= ' — ' . $price;
            }
            $lines[] = $line;
            $actions[] = [
                'label' => mb_strlen($label) > 28 ? mb_substr($label, 0, 25) . '…' : ($label ?: ($ar ? 'عرض المنتج' : 'View product')),
                'href' => '/products/' . $product->id,
            ];
        }

        $actions[] = ['label' => $ar ? 'كل المنتجات' : 'All products', 'href' => '/products'];
        $actions[] = $this->whatsappAction($ar, $whatsapp);

        return $this->pack(
            ($ar ? "وجدت هذه المنتجات:\n\n" : "I found these products:\n\n") . implode("\n", $lines),
            $actions
        );
    }

    private function extractSearchQuery(string $message): string
    {
        $text = mb_strtolower(trim($message));
        $stopWords = [
            'the', 'a', 'an', 'for', 'about', 'price', 'cost', 'how', 'much', 'what', 'is', 'are', 'do', 'you', 'have',
            'search', 'find', 'show', 'me', 'need', 'want', 'looking', 'product', 'products',
            'ما', 'هل', 'عندكم', 'عندك', 'في', 'من', 'على', 'عن', 'سعر', 'كم', 'ابحث', 'بحث', 'اريد', 'أريد',
            'منتج', 'منتجات', 'عند', 'يوجد', 'متوفر', 'available',
        ];

        $words = preg_split('/\s+/u', $text) ?: [];
        $filtered = array_values(array_filter($words, function (string $word) use ($stopWords) {
            $word = trim($word);
            return $word !== '' && mb_strlen($word) >= 2 && !in_array($word, $stopWords, true);
        }));

        return trim(implode(' ', $filtered));
    }

    /** @return list<array{id: int, name: string, name_ar: ?string, brand: ?string, price: ?string}> */
    private function productsForAiContext(string $message): array
    {
        $query = $this->extractSearchQuery($message);
        if (mb_strlen($query) < 2) {
            return [];
        }

        return Product::visibleToPublic()
            ->where(function ($q) use ($query) {
                $like = '%' . $query . '%';
                $q->where('name', 'like', $like)
                    ->orWhere('name_ar', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('description_ar', 'like', $like);
            })
            ->orderBy('name_ar')
            ->limit(8)
            ->get(['id', 'name', 'name_ar', 'brand', 'price', 'price_number'])
            ->map(function (Product $product) {
                $price = $product->price ?: ($product->price_number
                    ? number_format((float) $product->price_number, 2) . ' AED'
                    : '');

                return [
                    'id' => (int) $product->id,
                    'name' => (string) ($product->name ?? ''),
                    'name_ar' => $product->name_ar,
                    'brand' => $product->brand,
                    'price' => $price,
                ];
            })
            ->all();
    }
}
