<?php

namespace App\Http\Controllers;

use App\Support\CompanySettings;
use App\Support\StorageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = CompanySettings::read();

        if ($settings === []) {
            return response()->json($this->defaultSettings());
        }

        return response()->json(array_merge($settings, CompanySettings::brandingResponse($settings)));
    }

    public function companyInfoJson()
    {
        $settings = CompanySettings::read();

        if ($settings === []) {
            return response()->json($this->defaultSettings())
                ->header('Cache-Control', 'public, max-age=60');
        }

        return response()->json(array_merge($settings, CompanySettings::brandingResponse($settings)))
            ->header('Cache-Control', 'public, max-age=60');
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'companyName' => 'required|string|max:255',
                'companyNameAr' => 'required|string|max:255',
                'trn' => 'nullable|string|max:100',
                'tagline' => 'required|string|max:255',
                'taglineAr' => 'required|string|max:255',
                'description' => 'required|string',
                'descriptionAr' => 'required|string',
                'contact.email' => 'required|email',
                'contact.phone' => 'required|string',
                'contact.whatsapp' => 'required|string',
                'contact.address.en' => 'required|string',
                'contact.address.ar' => 'required|string',
                'workingHours.en' => 'required|string',
                'workingHours.ar' => 'required|string',
                'social.facebook' => 'nullable|string',
                'social.twitter' => 'nullable|string',
                'social.instagram' => 'nullable|string',
                'social.linkedin' => 'nullable|string',
                'about.en' => 'required|string',
                'about.ar' => 'required|string',
            ]);

            $validated['footerDescAr'] = $validated['descriptionAr'];

            $existing = CompanySettings::read();
            foreach (['logo', 'signature', 'signatureName'] as $brandingKey) {
                if (array_key_exists($brandingKey, $existing)) {
                    $validated[$brandingKey] = $existing[$brandingKey];
                }
            }

            $validated['seo'] = $request->input('seo', $existing['seo'] ?? [
                'keywords' => 'smart home, automation, Dubai, UAE, الإمارات, دبي, منزل ذكي, أتمتة',
                'location' => [
                    'city' => 'Dubai',
                    'cityAr' => 'دبي',
                    'country' => 'United Arab Emirates',
                    'countryAr' => 'الإمارات العربية المتحدة',
                    'countryCode' => 'AE',
                    'region' => 'Middle East',
                ],
            ]);

            CompanySettings::write($validated);

            Log::info('Settings saved successfully');

            return response()->json([
                'message' => 'Settings updated successfully',
                'data' => $validated,
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving settings', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'خطأ في حفظ الإعدادات: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateBranding(Request $request)
    {
        try {
            $request->validate([
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'signature' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'signature_name' => 'nullable|string|max:255',
            ]);

            if (!$request->hasFile('logo') && !$request->hasFile('signature') && !$request->filled('signature_name')) {
                return response()->json([
                    'error' => 'اختر شعاراً أو توقيعاً للرفع، أو أدخل اسم التوقيع.',
                ], 422);
            }

            $settings = CompanySettings::read();
            $destinationPath = CompanySettings::brandingDir();
            $previousLogo = $settings['logo'] ?? null;
            $previousSignature = $settings['signature'] ?? null;

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = 'logo-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $settings['logo'] = '/storage/branding/' . $filename;
            }

            if ($request->hasFile('signature')) {
                $file = $request->file('signature');
                $filename = 'signature-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $settings['signature'] = '/storage/branding/' . $filename;
            }

            if ($request->filled('signature_name')) {
                $settings['signatureName'] = $request->input('signature_name');
            }

            CompanySettings::write($settings);

            if ($request->hasFile('logo') && $previousLogo && $previousLogo !== ($settings['logo'] ?? null)) {
                self::deleteBrandingFile($previousLogo);
            }
            if ($request->hasFile('signature') && $previousSignature && $previousSignature !== ($settings['signature'] ?? null)) {
                self::deleteBrandingFile($previousSignature);
            }

            $saved = CompanySettings::read();

            return response()->json(array_merge([
                'message' => 'تم تحديث الشعار/التوقيع بنجاح',
            ], CompanySettings::brandingResponse($saved)));
        } catch (\Exception $e) {
            Log::error('Error saving branding', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'خطأ في حفظ الشعار/التوقيع: ' . $e->getMessage(),
            ], 500);
        }
    }

    private static function deleteBrandingFile(?string $webPath): void
    {
        $absolute = StorageUrl::toFilesystemPath($webPath);
        if ($absolute && File::exists($absolute)) {
            @unlink($absolute);
        }
    }

    /** @return array<string, mixed> */
    private function defaultSettings(): array
    {
        return [
            'companyName' => 'SMARTFLOW',
            'companyNameAr' => 'سمارت فلو',
            'tagline' => 'Your Home is Safe With Us',
            'taglineAr' => 'منزلك آمن معنا',
            'description' => 'Smart Home Solutions Provider in Dubai, UAE',
            'descriptionAr' => 'مزود الحلول المنزلية الذكية في دبي، الإمارات',
            'footerDescAr' => 'مزود الحلول المنزلية الذكية في دبي، الإمارات',
            'contact' => [
                'email' => 'info@smartflow.ae',
                'phone' => '+971 50 123 4567',
                'whatsapp' => '971501234567',
                'address' => [
                    'en' => 'Dubai, United Arab Emirates',
                    'ar' => 'دبي، الإمارات العربية المتحدة',
                ],
            ],
            'workingHours' => [
                'en' => 'Sunday - Thursday: 9:00 AM - 6:00 PM',
                'ar' => 'الأحد - الخميس: 9:00 صباحاً - 6:00 مساءً',
            ],
            'social' => [
                'facebook' => '',
                'twitter' => '',
                'instagram' => '',
                'linkedin' => '',
            ],
            'about' => [
                'en' => 'SmartFlow provides innovative smart home solutions.',
                'ar' => 'تقدم سمارت فلو حلول منزلية ذكية مبتكرة.',
            ],
            'seo' => [
                'keywords' => 'smart home, Dubai, UAE',
                'location' => [
                    'city' => 'Dubai',
                    'cityAr' => 'دبي',
                    'country' => 'United Arab Emirates',
                    'countryAr' => 'الإمارات العربية المتحدة',
                    'countryCode' => 'AE',
                    'region' => 'Middle East',
                ],
            ],
            'logo' => null,
            'signature' => null,
            'signatureName' => null,
        ];
    }
}
