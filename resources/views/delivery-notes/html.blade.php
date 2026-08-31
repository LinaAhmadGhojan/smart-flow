<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $noteNumber }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet"/>
@if(!empty($fontEmbedCss))
<style>{!! $fontEmbedCss !!}</style>
@endif
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
        background: #fff;
        color: #1a437f;
        font-family: 'Cairo', 'CairoFallback', 'Segoe UI', sans-serif;
        -webkit-font-smoothing: antialiased;
    }
    .page-wrap {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        background: #f4f7fb;
        padding: 16px;
    }
    .sheet {
        width: 794px;
        max-width: 100%;
        min-height: 1123px;
        background: #fff;
        padding: 12px 18px 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
        padding-bottom: 78px;
    }
    /* Applied in-browser before PDF capture (same idea as payment receipt). */
    body.dn-capture .page-wrap {
        min-height: auto !important;
        display: block !important;
        background: #fff !important;
        padding: 0 !important;
    }
    body.dn-capture .sheet {
        width: 794px !important;
        max-width: 794px !important;
        overflow: visible !important;
        margin: 0 auto;
        box-shadow: none !important;
    }
    .en { direction: ltr; unicode-bidi: embed; display: inline-block; }

    .header {
        width: 100%;
        border: none;
        margin-bottom: 12px;
        border-collapse: collapse;
        direction: ltr;
        table-layout: fixed;
    }
    .header td { vertical-align: middle; padding: 0; border: none; }
    .brand { width: 56%; padding: 4px 12px 4px 0; }
    .brand-inner { width: 100%; border-collapse: collapse; direction: ltr; }
    .brand-inner td { border: none; vertical-align: middle; padding: 0; }
    .logo-td { width: 175px; padding-right: 12px; }
    .brand img.logo { width: 175px; height: auto; max-width: 175px; max-height: 120px; display: block; object-fit: contain; }
    .brand-text { direction: ltr; text-align: left; }
    .brand-name {
        font-size: 22px; font-weight: 700; color: #1a437f;
        line-height: 1.2; text-align: left; direction: rtl;
        margin-bottom: 4px;
    }
    .brand-contact { margin-top: 2px; font-size: 12px; color: #1a437f; }
    .brand-contact .row {
        display: flex; align-items: center; justify-content: flex-start;
        gap: 8px; margin: 2px 0; white-space: nowrap; direction: ltr;
    }
    .brand-contact img { height: 15px; width: 15px; }

    .doc-cell {
        width: 44%;
        text-align: right;
        direction: rtl;
        padding: 4px 2px 4px 14px;
        border-left: 1.5px solid #c9d9eb;
    }
    .doc-title-row {
        display: flex; align-items: center; gap: 10px;
        justify-content: center; margin-bottom: 8px;
    }
    .doc-title { text-align: center; }
    .doc-title .ar-title { font-size: 24px; font-weight: 700; color: #1a437f; line-height: 1.05; }
    .doc-title .en-title {
        font-size: 12px; font-weight: 700; letter-spacing: 0.06em;
        color: #2f5f9e; margin-top: 2px;
    }
    .truck {
        width: 44px; height: 44px; border-radius: 50%; background: #1a437f;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .truck svg { width: 24px; height: 24px; fill: #fff; }
    .dn-meta { margin-top: 2px; }
    .dn-num-row, .dn-date {
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .dn-num-lab { font-size: 12px; font-weight: 700; white-space: nowrap; color: #1a437f; }
    .dn-box {
        border: 1.5px solid #1a437f;
        border-radius: 8px;
        padding: 4px 16px;
        text-align: center;
        font-weight: 700;
        font-size: 13px;
        direction: ltr;
        color: #1a437f;
        min-width: 132px;
        line-height: 1.3;
        background: #fff;
    }
    .dn-date { margin-top: 7px; font-size: 12px; font-weight: 700; }
    .dn-date .dots {
        border-bottom: 1.4px dotted #8faed0;
        padding: 0 6px 1px;
        min-width: 132px;
        text-align: center;
        font-weight: 700;
        color: #1a437f;
    }

    .cards { width: 100%; border-collapse: separate; border-spacing: 10px 0; margin: 0 -5px 10px; direction: ltr; }
    .cards td { width: 50%; vertical-align: top; padding: 0; border: none; direction: rtl; height: 1px; }
    .card {
        border: 1.4px solid #1a437f; border-radius: 10px; overflow: hidden;
        height: 100%;
        display: flex; flex-direction: column;
        box-sizing: border-box;
    }
    .card-h {
        background: #1a437f; color: #fff; font-weight: 700; font-size: 12px;
        padding: 7px 12px; display: flex; align-items: center; gap: 8px;
        flex-shrink: 0;
    }
    .card-h svg { width: 14px; height: 14px; fill: #fff; }
    .card-b { padding: 7px 12px 9px; flex: 1; }
    .fline { width: 100%; border-collapse: collapse; margin: 3px 0; }
    .fline td { border: none; padding: 3px 0; vertical-align: bottom; font-size: 11px; }
    .fline .lab { white-space: nowrap; width: 1%; padding-left: 8px; font-weight: 700; }
    .fline .val {
        border-bottom: 1.3px dotted #8faed0; width: 99%; padding: 0 6px 2px;
        font-weight: 600; color: #0f3266; min-height: 16px;
        height: 18px; line-height: 16px;
    }

    .ack { font-size: 11px; font-weight: 700; text-align: center; margin: 4px 0 8px; color: #1a437f; }

    .grid { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 8px; }
    .grid th {
        background: #1a437f; color: #fff; font-size: 11px; font-weight: 700;
        padding: 7px 5px; text-align: center; border: 1px solid #1a437f;
    }
    .grid td {
        border: 1px solid #b8cce8; padding: 5px 5px; text-align: center;
        font-size: 10.5px; height: 22px; color: #1a437f; background: #fff;
    }
    .grid tr:nth-child(even) td { background: #f3f7fc; }
    .grid .desc { text-align: right; padding-right: 8px; }
    .grid .sn { width: 28px; }

    .mid { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-bottom: 8px; }
    .mid td { vertical-align: top; border: none; padding: 0; }
    .box { border: 1.3px solid #1a437f; border-radius: 8px; overflow: hidden; min-height: 108px; }
    .box-h { background: #1a437f; color: #fff; font-size: 11px; font-weight: 700; padding: 6px 10px; text-align: center; }
    .box-b { padding: 8px 10px; }
    .tot { width: 100%; border-collapse: collapse; }
    .tot td { border: 1px solid #b8cce8; padding: 5px 8px; font-size: 10.5px; }
    .tot .k { font-weight: 700; text-align: right; width: 62%; background: #fff; }
    .tot .v { text-align: center; direction: ltr; min-height: 18px; }
    .tot tr.grand td { background: #1a437f; color: #fff; font-weight: 700; }
    .note-lines { border-bottom: 1.2px dotted #8faed0; min-height: 18px; margin: 4px 0; font-size: 10.5px; padding: 0 4px; }
    .pay-row { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .pay-row td { border: none; text-align: center; padding: 4px 2px; width: 33%; }
    .pay-row img { height: 34px; width: 38px; object-fit: contain; display: block; margin: 0 auto 4px; }
    .pay-row .lbl { font-size: 11px; font-weight: 700; }
    .chk {
        display: inline-block; width: 13px; height: 13px; border: 1.4px solid #1a437f;
        border-radius: 2px; margin-top: 4px; background: #fff;
    }

    .signs-wrap {
        background: #eef4fb;
        border-radius: 10px;
        margin: auto 0 8px;
        overflow: hidden;
    }
    .signs { width: 100%; border-collapse: collapse; }
    .signs td {
        width: 33.33%; border: none; border-left: 1px solid #c5d8ef;
        vertical-align: top; padding: 8px 12px 10px; text-align: center;
    }
    .signs td:first-child { border-left: none; }
    .sig-head {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        margin-bottom: 8px;
    }
    .sig-ico { width: 15px; height: 15px; display: block; fill: none; stroke: #1a437f; stroke-width: 1.6; }
    .sig-ttl { font-size: 11px; font-weight: 700; }
    .sig-line { border-bottom: 1.2px dotted #8faed0; height: 18px; margin: 6px 0; text-align: right; font-size: 10px; padding: 0 4px; }
    .stamp-box {
        border: 1.3px dashed #8faed0; height: 62px; border-radius: 4px; margin-top: 6px;
        background: #fff;
        display: flex; align-items: center; justify-content: center; overflow: hidden;
    }
    .stamp-box img {
        max-width: 100%; max-height: 100%; object-fit: contain; display: block;
    }

    .footer {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
        height: 72px;
        overflow: hidden;
    }
    .thanks-bar {
        position: absolute;
        top: 6px;
        left: 50%;
        transform: translateX(-50%);
        background: #fff;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 700;
        color: #1a437f;
        white-space: nowrap;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .thanks-bar .line { width: 42px; height: 1.2px; background: #1a437f; }
    .thanks-bar .leaf { width: 16px; height: 16px; display: block; }
    .wave-wrap { position: absolute; left: 0; right: 0; bottom: 0; height: 52px; line-height: 0; }
    .wave-wrap img { width: 100%; height: 52px; display: block; object-fit: fill; }
    .circuit {
        position: absolute; left: 6px; bottom: 3px; width: 150px; height: 40px;
        opacity: 0.75; pointer-events: none;
    }

    @media print {
        @page { size: A4 portrait; margin: 0; }
        html, body, .page-wrap {
            background: #fff !important; padding: 0 !important; margin: 0 !important;
        }
        .page-wrap { display: block !important; min-height: auto !important; }
        .sheet {
            width: 210mm !important; min-height: 297mm !important; height: 297mm !important;
            padding: 10px 12px 0 !important; margin: 0 !important;
            display: flex !important; flex-direction: column !important;
            padding-bottom: 70px !important;
        }
        .footer { margin: 0 !important; height: 68px !important; left: 0 !important; right: 0 !important; bottom: 0 !important; }
        .wave-wrap { height: 50px !important; }
        .wave-wrap img { height: 50px !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>
</head>
<body>
<div class="page-wrap">
<div class="sheet">

<table class="header">
    <tr>
        <td class="brand">
            <table class="brand-inner">
                <tr>
                    <td class="logo-td">
                        @if(!empty($logoDataUri))
                            <img class="logo" src="{{ $logoDataUri }}" alt="">
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
                <div class="truck">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.2 9.2 h2.2 M1.6 11.4 h2.4 M2 13.6 h2.1" stroke="#fff" stroke-width="1.3" stroke-linecap="round" fill="none"/>
                        <path d="M5 8.2 h9.2 v7.2 H5 V8.2zm9.2 2.6 h3.2 L20.2 13.8 V15.4 h-6 V10.8z"/>
                        <circle cx="7.4" cy="17.4" r="1.45"/>
                        <circle cx="16.6" cy="17.4" r="1.45"/>
                    </svg>
                </div>
                <div class="doc-title">
                    <div class="ar-title">دليفري نوت</div>
                    <div class="en-title">DELIVERY NOTE</div>
                </div>
            </div>
            <div class="dn-meta">
                <div class="dn-num-row">
                    <span class="dn-num-lab">رقم الدليفري نوت</span>
                    <div class="dn-box">{{ $noteNumber }}</div>
                </div>
                <div class="dn-date">
                    <span class="dn-num-lab">التاريخ :</span>
                    <span class="dots en">{{ $dateLabel }}</span>
                </div>
            </div>
        </td>
    </tr>
</table>

<table class="cards">
    <tr>
        <td>
            <div class="card">
                <div class="card-h">
                    <svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/></svg>
                    بيانات العميل
                </div>
                <div class="card-b">
                    <table class="fline"><tr><td class="lab">اسم العميل :</td><td class="val">{{ $clientName }}</td></tr></table>
                    <table class="fline"><tr><td class="lab">اسم الشركة :</td><td class="val">{{ $clientCompany }}</td></tr></table>
                    <table class="fline"><tr><td class="lab">العنوان :</td><td class="val">{{ $clientAddress }}</td></tr></table>
                    <table class="fline"><tr><td class="lab">رقم التواصل :</td><td class="val"><span class="en">{{ $clientPhone }}</span></td></tr></table>
                    <table class="fline"><tr><td class="lab">رقم الهاتف :</td><td class="val"><span class="en">{{ $clientMobile }}</span></td></tr></table>
                    <table class="fline"><tr><td class="lab">البريد الإلكتروني :</td><td class="val"><span class="en">{{ $clientEmail }}</span></td></tr></table>
                </div>
            </div>
        </td>
        <td>
            <div class="card">
                <div class="card-h">
                    <svg viewBox="0 0 24 24"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg>
                    بيانات التسليم
                </div>
                <div class="card-b">
                    <table class="fline"><tr><td class="lab">جهة التسليم :</td><td class="val">{{ $deliveryTo }}</td></tr></table>
                    <table class="fline"><tr><td class="lab">العنوان :</td><td class="val">{{ $deliveryAddress }}</td></tr></table>
                    <table class="fline"><tr><td class="lab">رقم التواصل :</td><td class="val"><span class="en">{{ $deliveryPhone }}</span></td></tr></table>
                    <table class="fline"><tr><td class="lab">طريقة التسليم :</td><td class="val">{{ $deliveryMethod }}</td></tr></table>
                    <table class="fline"><tr><td class="lab">ملاحظات التسليم :</td><td class="val">@if(filled($deliveryNotes)){{ $deliveryNotes }}@else&nbsp;@endif</td></tr></table>
                    <table class="fline"><tr><td class="lab"></td><td class="val">&nbsp;</td></tr></table>
                </div>
            </div>
        </td>
    </tr>
</table>

<table class="grid">
    <tr>
        <th class="sn">م</th>
        <th>الصنف / الوصف</th>
        <th style="width:16%">الموديل / الكود</th>
        <th style="width:10%">الكمية</th>
        <th style="width:10%">الوحدة</th>
        <th style="width:16%">ملاحظات</th>
    </tr>
    @foreach($items as $i => $item)
    <tr>
        <td>{{ $i + 1 }}</td>
        <td class="desc">{{ $item['description'] }}</td>
        <td>{{ $item['code'] }}</td>
        <td><span class="en">{{ $item['quantity'] }}</span></td>
        <td>{{ $item['unit'] }}</td>
        <td>{{ $item['note'] }}</td>
    </tr>
    @endforeach
</table>

<p class="ack">أستلمت المواد المذكورة أعلاه بحالة جيدة وسليمة ومطابقة للمواصفات المطلوبة</p>

<table class="mid">
    <tr>
        <td style="width:32%">
            <div class="box">
                <table class="tot">
                    <tr><td class="k">إجمالي المبلغ</td><td class="v">{{ $totalAmount }}</td></tr>
                    <tr><td class="k">الخصم</td><td class="v">{{ $discountAmount }}</td></tr>
                    <tr><td class="k">ضريبة القيمة المضافة (5%)</td><td class="v">{{ $vatAmount }}</td></tr>
                    <tr class="grand"><td class="k">الإجمالي</td><td class="v">{{ $grandTotal }}</td></tr>
                </table>
            </div>
        </td>
        <td style="width:36%">
            <div class="box">
                <div class="box-h">ملاحظات عامة</div>
                <div class="box-b">
                    <div class="note-lines">{{ $notes }}</div>
                    <div class="note-lines"></div>
                    <div class="note-lines"></div>
                    <div class="note-lines"></div>
                </div>
            </div>
        </td>
        <td style="width:32%">
            <div class="box">
                <div class="box-h">طريقة الاستلام</div>
                <div class="box-b">
                    <table class="pay-row">
                        <tr>
                            <td>
                                <img src="{{ $iconCheque }}" alt="">
                                <div class="lbl">شيك</div>
                                <span class="chk"></span>
                            </td>
                            <td>
                                <img src="{{ $iconBank }}" alt="">
                                <div class="lbl">تحويل بنكي</div>
                                <span class="chk"></span>
                            </td>
                            <td>
                                <img src="{{ $iconCash }}" alt="">
                                <div class="lbl">كاش</div>
                                <span class="chk"></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </td>
    </tr>
</table>

<div class="signs-wrap">
<table class="signs">
    <tr>
        <td>
            <div class="sig-head">
                <svg class="sig-ico" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.2"/><path d="M5.5 19c.8-3.2 3.3-5 6.5-5s5.7 1.8 6.5 5"/></svg>
                <div class="sig-ttl">تسليم بواسطة</div>
            </div>
            <div class="sig-line">الاسم : {{ $deliveredBy }}</div>
            <div class="sig-line">التوقيع :</div>
            <div class="sig-line">التاريخ : <span class="en">{{ $dateLabel }}</span></div>
        </td>
        <td>
            <div class="sig-head">
                <svg class="sig-ico" viewBox="0 0 24 24"><rect x="7" y="3.5" width="10" height="12" rx="1.2"/><path d="M9 7.5h6M9 10h6M9 12.5h4"/><path d="M8 16.5h8v2.2c0 .7-1.8 1.5-4 1.5s-4-.8-4-1.5V16.5z"/></svg>
                <div class="sig-ttl">توقيع الشركة</div>
            </div>
            <div class="stamp-box">
                @if(!empty($signatureDataUri))
                    <img src="{{ $signatureDataUri }}" alt="">
                @endif
            </div>
        </td>
        <td>
            <div class="sig-head">
                <svg class="sig-ico" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.2"/><path d="M5.5 19c.8-3.2 3.3-5 6.5-5s5.7 1.8 6.5 5"/></svg>
                <div class="sig-ttl">توقيع واستلام المستلم</div>
            </div>
            <div class="sig-line">الاسم : {{ $receivedBy }}</div>
            <div class="sig-line">التوقيع :</div>
            <div class="sig-line">التاريخ :</div>
        </td>
    </tr>
</table>
</div>

<div class="footer">
    <div class="thanks-bar">
        <span class="line"></span>
        <svg class="leaf" viewBox="0 0 24 24" fill="#1a437f">
            <path d="M12 2C8 6 6 10 6 14c0 3.3 2.7 6 6 8 3.3-2 6-4.7 6-8 0-4-2-8-6-12z"/>
            <path d="M12 8v12" stroke="#fff" stroke-width="1.6" fill="none"/>
            <path d="M12 11 L9.5 14.5 L12 13.2 L14.5 17 Z" fill="#fff"/>
        </svg>
        شكراً لتعاملكم معنا
        <svg class="leaf" viewBox="0 0 24 24" fill="#1a437f">
            <path d="M12 2C8 6 6 10 6 14c0 3.3 2.7 6 6 8 3.3-2 6-4.7 6-8 0-4-2-8-6-12z"/>
            <path d="M12 8v12" stroke="#fff" stroke-width="1.6" fill="none"/>
            <path d="M12 11 L9.5 14.5 L12 13.2 L14.5 17 Z" fill="#fff"/>
        </svg>
        <span class="line"></span>
    </div>
    <div class="wave-wrap" aria-hidden="true">
        <img src="{{ $waveSvg }}" alt="">
        <svg class="circuit" viewBox="0 0 150 42" xmlns="http://www.w3.org/2000/svg">
            <g fill="none" stroke="#9ec4ea" stroke-width="1.15">
                <path d="M2 36 H26 V18 H48 V30 H78 V14 H108"/>
                <path d="M16 36 V24 H38"/>
                <path d="M48 18 H66 V8 H90"/>
                <path d="M78 30 H96 V36 H124"/>
                <path d="M108 14 V6 H132"/>
            </g>
            <g fill="#c5def6">
                <circle cx="2" cy="36" r="2"/><circle cx="26" cy="36" r="2"/>
                <circle cx="26" cy="18" r="2"/><circle cx="48" cy="18" r="2"/>
                <circle cx="48" cy="30" r="2"/><circle cx="78" cy="30" r="2"/>
                <circle cx="78" cy="14" r="2"/><circle cx="108" cy="14" r="2"/>
                <circle cx="16" cy="24" r="1.6"/><circle cx="66" cy="8" r="1.6"/>
                <circle cx="90" cy="8" r="1.6"/><circle cx="96" cy="36" r="1.6"/>
                <circle cx="124" cy="36" r="1.6"/><circle cx="132" cy="6" r="1.6"/>
            </g>
        </svg>
    </div>
</div>

</div>
</div>
</body>
</html>
