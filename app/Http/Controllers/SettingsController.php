<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Get the correct path for company-info.json
     */
    private function getSettingsPath()
    {
        // جرب المسار العادي أولاً
        $path = public_path('company-info.json');
        
        // إذا لم يوجد، جرب المسار البديل للاستضافة
        if (!File::exists($path)) {
            $path = base_path('../company-info.json');
        }
        
        // إذا لم يوجد، جرب في public_html مباشرة
        if (!File::exists($path)) {
            $path = base_path('../public_html/company-info.json');
        }
        
        return $path;
    }

    /**
     * Get company settings
     */
    public function index()
    {
        $filePath = $this->getSettingsPath();
        
        if (!File::exists($filePath)) {
            // أرجع قيم افتراضية إذا لم يوجد الملف
            return response()->json([
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
                        'ar' => 'دبي، الإمارات العربية المتحدة'
                    ]
                ],
                'workingHours' => [
                    'en' => 'Sunday - Thursday: 9:00 AM - 6:00 PM',
                    'ar' => 'الأحد - الخميس: 9:00 صباحاً - 6:00 مساءً'
                ],
                'social' => [
                    'facebook' => '',
                    'twitter' => '',
                    'instagram' => '',
                    'linkedin' => ''
                ],
                'about' => [
                    'en' => 'SmartFlow provides innovative smart home solutions.',
                    'ar' => 'تقدم سمارت فلو حلول منزلية ذكية مبتكرة.'
                ],
                'seo' => [
                    'keywords' => 'smart home, Dubai, UAE',
                    'location' => [
                        'city' => 'Dubai',
                        'cityAr' => 'دبي',
                        'country' => 'United Arab Emirates',
                        'countryAr' => 'الإمارات العربية المتحدة',
                        'countryCode' => 'AE',
                        'region' => 'Middle East'
                    ]
                ],
                'logo' => null,
                'signature' => null,
                'signatureName' => null,
            ]);
        }

        $settings = json_decode(File::get($filePath), true);
        
        return response()->json($settings);
    }

    /**
     * Update company settings
     */
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

            // جرب المسارات المختلفة للحفظ
            $possiblePaths = [
                public_path('company-info.json'),
                base_path('../company-info.json'),
                base_path('../public_html/company-info.json'),
            ];

            $filePath = null;
            foreach ($possiblePaths as $path) {
                // تحقق إذا كان المسار قابل للكتابة أو إذا كان المجلد موجود
                $dir = dirname($path);
                if (File::exists($path) || (File::isDirectory($dir) && is_writable($dir))) {
                    $filePath = $path;
                    break;
                }
            }

            if (!$filePath) {
                // استخدم المسار الأول كافتراضي
                $filePath = $possiblePaths[0];
            }

            // Add footer description for backward compatibility
            $validated['footerDescAr'] = $validated['descriptionAr'];

            // Preserve branding fields (logo/signature) managed via updateBranding()
            $existing = File::exists($this->getSettingsPath())
                ? json_decode(File::get($this->getSettingsPath()), true)
                : [];
            foreach (['logo', 'signature', 'signatureName'] as $brandingKey) {
                if (isset($existing[$brandingKey])) {
                    $validated[$brandingKey] = $existing[$brandingKey];
                }
            }

            // Preserve or add SEO data
            $seoData = $request->input('seo', [
                'keywords' => 'smart home, automation, Dubai, UAE, الإمارات, دبي, منزل ذكي, أتمتة',
                'location' => [
                    'city' => 'Dubai',
                    'cityAr' => 'دبي',
                    'country' => 'United Arab Emirates',
                    'countryAr' => 'الإمارات العربية المتحدة',
                    'countryCode' => 'AE',
                    'region' => 'Middle East'
                ]
            ]);
            $validated['seo'] = $seoData;

            // Save to file with pretty formatting
            $json = json_encode($validated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            $result = File::put($filePath, $json);

            if ($result === false) {
                Log::error('Failed to write settings file', ['path' => $filePath]);
                return response()->json([
                    'error' => 'فشل في حفظ الملف - تحقق من صلاحيات الكتابة',
                    'path' => $filePath
                ], 500);
            }

            Log::info('Settings saved successfully', ['path' => $filePath]);

            return response()->json([
                'message' => 'Settings updated successfully',
                'data' => $validated
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving settings', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'خطأ في حفظ الإعدادات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload/update the company logo and signature used on exported PDF reports.
     */
    public function updateBranding(Request $request)
    {
        try {
            $request->validate([
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'signature' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'signature_name' => 'nullable|string|max:255',
            ]);

            $filePath = $this->getSettingsPath();
            $settings = File::exists($filePath) ? json_decode(File::get($filePath), true) : [];
            if (!is_array($settings)) {
                $settings = [];
            }

            $destinationPath = public_path('storage/branding');
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

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

            $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            File::put($filePath, $json);

            return response()->json([
                'message' => 'تم تحديث الشعار/التوقيع بنجاح',
                'logo' => $settings['logo'] ?? null,
                'signature' => $settings['signature'] ?? null,
                'signatureName' => $settings['signatureName'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving branding', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'خطأ في حفظ الشعار/التوقيع: ' . $e->getMessage()
            ], 500);
        }
    }
}
