<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>تقرير زيارة موقع {{ $reportNo }}</title>
<style>
    @font-face {
        font-family: 'ArabicReport';
        font-style: normal;
        font-weight: normal;
        src: url('{{ $arabicFontUrl }}') format('truetype');
    }
    @page { margin: 10mm 10mm 12mm 10mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'ArabicReport', DejaVu Sans, sans-serif;
        color: #1a437f;
        font-size: 10.5px;
        line-height: 1.45;
        direction: rtl;
    }
    .page {
        width: 100%;
        min-height: 277mm;
        position: relative;
        padding-bottom: 54px;
    }
    .en { direction: ltr; unicode-bidi: embed; font-family: DejaVu Sans, sans-serif; }

    /* Header */
    .header { width: 100%; border-collapse: collapse; margin-bottom: 10px; direction: ltr; table-layout: fixed; }
    .header td { vertical-align: middle; border: none; padding: 0; }
    .brand { width: 52%; padding: 2px 10px 2px 0; }
    .brand-inner { width: 100%; border-collapse: collapse; direction: ltr; }
    .brand-inner td { border: none; vertical-align: middle; padding: 0; }
    .logo-td { width: 92px; padding-right: 10px; }
    .logo-td img { width: 88px; height: auto; max-height: 88px; display: block; }
    .brand-text { direction: rtl; text-align: right; }
    .brand-name { font-size: 20px; font-weight: 700; color: #1a437f; line-height: 1.15; margin-bottom: 2px; }
    .brand-contact { font-size: 10px; color: #1a437f; }
    .brand-contact .row { margin: 2px 0; white-space: nowrap; direction: ltr; text-align: left; }
    .brand-contact img { width: 11px; height: 11px; vertical-align: middle; margin-right: 5px; }

    .doc-cell {
        width: 48%;
        border-left: 1.5px solid #c9d9eb;
        padding: 2px 0 2px 12px;
        direction: rtl;
        vertical-align: top;
    }
    .doc-title-row { text-align: center; margin-bottom: 8px; }
    .doc-title-row table { margin: 0 auto; border-collapse: collapse; }
    .doc-title-row td { border: none; vertical-align: middle; padding: 0 4px; }
    .doc-ico {
        width: 34px; height: 34px; border-radius: 50%; background: #1a437f;
        text-align: center; line-height: 34px; color: #fff; font-size: 16px;
    }
    .doc-title { font-size: 22px; font-weight: 700; color: #1a437f; line-height: 1.1; }

    .meta {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        direction: rtl;
        margin-top: 2px;
    }
    .meta col { width: 50%; }
    .meta tr { height: 30px; }
    .meta td.lab,
    .meta td.val {
        display: table-cell !important;
        border: 1px solid #b8cce8;
        padding: 0 8px;
        font-size: 10px;
        vertical-align: middle;
        width: 50%;
        height: 30px;
        max-height: 30px;
        min-height: 30px;
        line-height: 30px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        box-sizing: border-box;
    }
    .meta .lab {
        background: #f4f8fd;
        font-weight: 700;
        text-align: right;
    }
    .meta .val {
        text-align: center;
        font-weight: 600;
        color: #0f3266;
        background: #fff;
    }

    /* Body columns */
    .body { width: 100%; border-collapse: collapse; table-layout: fixed; direction: ltr; }
    .body td { vertical-align: top; border: none; padding: 0; }
    .photos-col { width: 32%; padding-right: 10px; direction: rtl; }
    .content-col { width: 68%; padding-left: 6px; direction: rtl; }

    .photos-box {
        border: 1.5px solid #1a437f;
        border-radius: 12px;
        background: #fff;
        position: relative;
        padding: 20px 12px 12px;
        margin: 5px;
    }
    .photos-head-wrap {
        position: absolute;
        top: -11px;
        left: 0;
        right: 0;
        text-align: center;
    }
    .photos-head {
        display: inline-block;
        background: #1a437f;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 18px;
        border-radius: 4px;
    }
    .photos-inner { width: 100%; }
    .photo-item {
        position: relative;
        margin: 0 0 10px;
        border-radius: 8px;
        overflow: hidden;
        height: 108px;
        background: #f4f8fd;
    }
    .photo-item:last-child { margin-bottom: 0; }
    .photo-item img { width: 100%; height: 108px; object-fit: cover; display: block; border-radius: 8px; }
    .photo-num {
        position: absolute;
        top: 5px;
        left: 5px;
        width: 20px;
        height: 20px;
        background: #1a437f;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        text-align: center;
        line-height: 20px;
        border-radius: 4px;
        z-index: 2;
    }

    .section { margin-bottom: 8px; }
    .section-head {
        display: flex;
        align-items: center;
        direction: rtl;
        margin-bottom: 4px;
    }
    .section-head-label {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
        background: #fff;
        padding: 0 3px;
    }
    .section-head-line {
        flex: 1;
        border-top: 1.5px solid #1a437f;
        height: 0;
        min-width: 0;
    }
    .section-ico {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }
    .section-ico svg { width: 20px; height: 20px; display: block; }
    .section-title {
        font-family: 'Tajawal', 'ArabicReport', sans-serif;
        font-size: 12px;
        font-weight: 700;
        color: #1a2b56;
        line-height: 1.2;
        white-space: nowrap;
    }

    .site-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        direction: rtl;
    }
    .site-table tr { height: 30px; }
    .site-table td {
        border: 1px solid #b8cce8;
        padding: 0 8px;
        font-size: 10px;
        vertical-align: middle;
        height: 30px;
        max-height: 30px;
        line-height: 30px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .site-table .k {
        background: #f4f8fd;
        font-weight: 700;
        width: 50%;
        text-align: right;
    }
    .site-table .v {
        width: 50%;
        text-align: center;
        color: #0f3266;
        font-weight: 600;
    }

    .bullets { margin: 0; padding: 0 16px 0 0; list-style: none; }
    .bullets li {
        position: relative;
        padding: 1px 0 3px 0;
        font-size: 10px;
        line-height: 1.45;
        color: #0f3266;
    }
    .bullets li:before {
        content: "•";
        color: #1a437f;
        font-weight: 700;
        position: absolute;
        right: -12px;
    }

    /* Signatures */
    .signs { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .signs td {
        width: 33.33%;
        vertical-align: top;
        text-align: center;
        border: none;
        padding: 0 8px;
        border-left: 1px dashed #8faed0;
    }
    .signs td:first-child { border-left: none; }
    .sig-head { margin-bottom: 6px; }
    .sig-head table { margin: 0 auto; border-collapse: collapse; }
    .sig-head td { border: none; padding: 0 3px; vertical-align: middle; }
    .sig-ico {
        width: 20px; height: 20px; border-radius: 50%; background: #1a437f;
        text-align: center; line-height: 20px; color: #fff; font-size: 10px;
    }
    .sig-ttl { font-size: 10px; font-weight: 700; color: #1a437f; }
    .sig-line {
        border-bottom: 1px dotted #8faed0;
        min-height: 16px;
        margin: 5px 0;
        text-align: right;
        font-size: 9.5px;
        color: #1a437f;
        padding: 0 2px 2px;
    }
    .stamp-box {
        width: 140px;
        height: 100px;
        border: 1.5px solid #b8cce8;
        border-radius: 4px;
        margin: 8px auto 4px;
        background: #fafcff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .stamp-box img {
        max-width: 100%;
        max-height: 92px;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    /* Footer */
    .footer-wrap {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        height: 46px;
        overflow: hidden;
    }
    .footer-bar {
        width: 100%;
        height: 46px;
        background: #1a437f;
        border-collapse: collapse;
    }
    .footer-bar td { border: none; vertical-align: middle; padding: 0; }
    .footer-side { width: 18%; height: 46px; overflow: hidden; }
    .footer-side img { width: 100%; height: 46px; object-fit: cover; opacity: 0.35; }
    .footer-center {
        text-align: center;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }
    .footer-bolt { color: #fff; font-size: 12px; padding: 0 8px; }
</style>
</head>
<body>
<div class="page">

<!-- Header -->
<table class="header">
    <tr>
        <td class="brand">
            <table class="brand-inner">
                <tr>
                    <td class="logo-td">
                        @if(!empty($logoDataUri))
                            <img src="{{ $logoDataUri }}" alt="logo">
                        @endif
                    </td>
                    <td class="brand-text">
                        <div class="brand-name">{{ $companyNameAr }}</div>
                        <div class="brand-contact">
                            <div class="row"><img src="{{ $iconPhone }}" alt=""><span class="en">{{ $phone }}</span></div>
                            <div class="row"><img src="{{ $iconEmail }}" alt=""><span class="en">{{ $email }}</span></div>
                            <div class="row"><img src="{{ $iconLocation }}" alt=""><span>{{ $addressAr }}</span></div>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="doc-cell">
            <div class="doc-title-row">
                <table>
                    <tr>
                        <td><div class="doc-ico">R</div></td>
                        <td><div class="doc-title">تقرير زيارة موقع</div></td>
                    </tr>
                </table>
            </div>
            <table class="meta">
                <colgroup><col><col></colgroup>
                <tr>
                    <td class="lab">رقم التقرير</td>
                    <td class="val"><span class="en">{{ $reportNo }}</span></td>
                </tr>
                <tr>
                    <td class="lab">تاريخ الزيارة</td>
                    <td class="val"><span class="en">{{ $visitDateLabel }}</span></td>
                </tr>
                <tr>
                    <td class="lab">وقت الزيارة</td>
                    <td class="val">{{ $visitTimeLabel }}</td>
                </tr>
                <tr>
                    <td class="lab">نوع الزيارة</td>
                    <td class="val">{{ $visitTypeLabel }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Body -->
<table class="body">
    <tr>
        <!-- Photos (left in LTR = visual left) -->
        <td class="photos-col">
            <div class="photos-box">
                <div class="photos-head-wrap">
                    <div class="photos-head">صور الزيارة</div>
                </div>
                <div class="photos-inner">
                @foreach($imageDataUris as $idx => $img)
                <div class="photo-item">
                    <div class="photo-num">{{ $idx + 1 }}</div>
                    @if($img)
                        <img src="{{ $img }}" alt="">
                    @endif
                </div>
                @endforeach
                </div>
            </div>
        </td>

        <!-- Content (right) -->
        <td class="content-col">

            <!-- Site info -->
            <div class="section">
                <div class="section-head">
                    <div class="section-head-label">
                        <span class="section-title">معلومات الموقع</span>
                        <span class="section-ico">&#128205;</span>
                    </div>
                    <div class="section-head-line"></div>
                </div>
                <table class="site-table">
                    <tr><td class="k">جهة الاستلام</td><td class="v">{{ $recipientEntity }}</td></tr>
                    <tr><td class="k">العنوان</td><td class="v">{{ $siteAddress }}</td></tr>
                    <tr><td class="k">اسم الشركة</td><td class="v">{{ $siteCompany }}</td></tr>
                    <tr><td class="k">رقم التواصل</td><td class="v en">{{ $contactPhone }}</td></tr>
                    <tr><td class="k">طريقة التسليم</td><td class="v">{{ $deliveryMethod }}</td></tr>
                    <tr><td class="k">ملاحظات التسليم</td><td class="v">{{ $deliveryNotes }}</td></tr>
                </table>
            </div>

            <!-- Executed works -->
            <div class="section">
                <div class="section-head">
                    <div class="section-head-label">
                        <span class="section-title">الأعمال المنفذة</span>
                        <span class="section-ico">&#10003;</span>
                    </div>
                    <div class="section-head-line"></div>
                </div>
                @if(count($executedWorksLines))
                <ul class="bullets">
                    @foreach($executedWorksLines as $line)
                    <li>{{ $line }}</li>
                    @endforeach
                </ul>
                @else
                <ul class="bullets"><li>—</li></ul>
                @endif
            </div>

            <!-- Notes -->
            <div class="section">
                <div class="section-head">
                    <div class="section-head-label">
                        <span class="section-title">الملاحظات</span>
                        <span class="section-ico">!</span>
                    </div>
                    <div class="section-head-line"></div>
                </div>
                @if(count($notesLines))
                <ul class="bullets">
                    @foreach($notesLines as $line)
                    <li>{{ $line }}</li>
                    @endforeach
                </ul>
                @else
                <ul class="bullets"><li>—</li></ul>
                @endif
            </div>

            <!-- Recommendations -->
            <div class="section">
                <div class="section-head">
                    <div class="section-head-label">
                        <span class="section-title">التوصيات والإجراءات المطلوبة</span>
                        <span class="section-ico">&#9881;</span>
                    </div>
                    <div class="section-head-line"></div>
                </div>
                @if(count($recommendationsLines))
                <ul class="bullets">
                    @foreach($recommendationsLines as $line)
                    <li>{{ $line }}</li>
                    @endforeach
                </ul>
                @else
                <ul class="bullets"><li>—</li></ul>
                @endif
            </div>

        </td>
    </tr>
</table>

<!-- Signatures -->
<table class="signs">
    <tr>
        <td>
            <div class="sig-head">
                <table><tr>
                    <td><div class="sig-ico">&#9679;</div></td>
                    <td><div class="sig-ttl">توقيع المهندس المسؤول</div></td>
                </tr></table>
            </div>
            <div class="sig-line">الاسم : {{ $engineerName }}</div>
            <div class="sig-line">التوقيع :</div>
            <div class="sig-line">التاريخ :</div>
        </td>
        <td>
            <div class="sig-head">
                <table><tr>
                    <td><div class="sig-ico">&#9632;</div></td>
                    <td><div class="sig-ttl">ختم الشركة</div></td>
                </tr></table>
            </div>
            <div class="stamp-box"></div>
        </td>
        <td>
            <div class="sig-head">
                <table><tr>
                    <td><div class="sig-ico">&#9679;</div></td>
                    <td><div class="sig-ttl">توقيع واستلام المستلم</div></td>
                </tr></table>
            </div>
            <div class="sig-line">الاسم :</div>
            <div class="sig-line">التوقيع :</div>
            <div class="sig-line">التاريخ :</div>
        </td>
    </tr>
</table>

</div>

<!-- Footer -->
<div class="footer-wrap">
    <table class="footer-bar">
        <tr>
            <td class="footer-side">
                @if(!empty($waveSvg))<img src="{{ $waveSvg }}" alt="">@endif
            </td>
            <td class="footer-center">
                <span class="footer-bolt">&#9889;</span>
                شكراً لتعاملكم معنا
                <span class="footer-bolt">&#9889;</span>
            </td>
            <td class="footer-side">
                @if(!empty($waveSvg))<img src="{{ $waveSvg }}" alt="">@endif
            </td>
        </tr>
    </table>
</div>

</body>
</html>
