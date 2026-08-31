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
                <div class="doc-ico-circle">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 5.5h8l2 2v11.5H8V5.5zm8 0v2.5h2" fill="#fff"/>
                        <path d="M10 11.5h6M10 14h4.5M10 16.5h5" stroke="#fff" stroke-width="1.4" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="doc-title">
                    <div class="ar-title">تقرير زيارة موقع</div>
                    <div class="en-title">SITE VISIT REPORT</div>
                </div>
            </div>
            <div class="doc-meta">
                <div class="meta-row">
                    <span class="meta-lab">رقم التقرير</span>
                    <div class="meta-box"><span class="en">{{ $reportNo }}</span></div>
                </div>
                <div class="meta-row">
                    <span class="meta-lab">تاريخ الزيارة</span>
                    <div class="meta-box"><span class="en">{{ $visitDateLabel }}</span></div>
                </div>
                <table class="meta-extra">
                    <colgroup><col><col></colgroup>
                    <tr><td class="lab">وقت الزيارة</td><td class="val">{{ $visitTimeLabel }}</td></tr>
                    <tr><td class="lab">نوع الزيارة</td><td class="val">{{ $visitTypeLabel }}</td></tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<div class="main-area">
<table class="body">
    <tr>
        <td class="photos-col">
            <div class="photos-box">
                <div class="photos-head-wrap">
                    <div class="photos-head">صور الزيارة</div>
                </div>
                <div class="photos-inner">
                @foreach($imageDataUris as $idx => $img)
                <div class="photo-item">
                    <div class="photo-num">{{ $idx + 1 }}</div>
                    @if($img)<img src="{{ $img }}" alt="">@endif
                </div>
                @endforeach
                </div>
            </div>
        </td>
        <td class="content-col">

            <div class="section">
                <div class="section-head">
                    <div class="section-head-label">
                        <span class="section-title">معلومات الموقع</span>
                        <span class="section-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="#1a437f" stroke-width="1.5"/><path d="M12 6a2.8 2.8 0 0 0-2.8 2.8c0 2.1 2.8 6.2 2.8 6.2s2.8-4.1 2.8-6.2A2.8 2.8 0 0 0 12 6z" fill="#1a437f"/><circle cx="12" cy="8.8" r="1" fill="#fff"/></svg></span>
                    </div>
                    <div class="section-head-line"></div>
                </div>
                <table class="site-table">
                    <colgroup><col><col></colgroup>
                    <tr><td class="k">جهة الاستلام</td><td class="v">{{ $recipientEntity }}</td></tr>
                    <tr><td class="k">العنوان</td><td class="v">{{ $siteAddress }}</td></tr>
                    <tr><td class="k">اسم الشركة</td><td class="v">{{ $siteCompany }}</td></tr>
                    <tr><td class="k">رقم التواصل</td><td class="v"><span class="en">{{ $contactPhone }}</span></td></tr>
                    <tr><td class="k">طريقة التسليم</td><td class="v">{{ $deliveryMethod }}</td></tr>
                    <tr><td class="k">ملاحظات التسليم</td><td class="v">{{ $deliveryNotes }}</td></tr>
                </table>
            </div>

            <div class="section">
                <div class="section-head">
                    <div class="section-head-label">
                        <span class="section-title">الأعمال المنفذة</span>
                        <span class="section-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="#1a437f" stroke-width="1.5"/><path d="M8 12.2l2.2 2.2 5.8-5.8" fill="none" stroke="#1a437f" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    </div>
                    <div class="section-head-line"></div>
                </div>
                @if(count($executedWorksLines))
                <ul class="bullets">@foreach($executedWorksLines as $line)<li>{{ $line }}</li>@endforeach</ul>
                @else<ul class="bullets"><li>—</li></ul>@endif
            </div>

            <div class="section">
                <div class="section-head">
                    <div class="section-head-label">
                        <span class="section-title">الملاحظات</span>
                        <span class="section-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="#1a437f" stroke-width="1.5"/><path d="M12 10v4M12 8h.01" stroke="#1a437f" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                    </div>
                    <div class="section-head-line"></div>
                </div>
                @if(count($notesLines))
                <ul class="bullets">@foreach($notesLines as $line)<li>{{ $line }}</li>@endforeach</ul>
                @else<ul class="bullets"><li>—</li></ul>@endif
            </div>

            <div class="section">
                <div class="section-head">
                    <div class="section-head-label">
                        <span class="section-title">التوصيات والإجراءات المطلوبة</span>
                        <span class="section-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="#1a437f" stroke-width="1.5"/><path d="M14.5 7.5a1 1 0 0 0 0 1.4l.9.9a1 1 0 0 0 1.4 0l2.4-2.4a1 1 0 0 0 0-1.4l-.9-.9a1 1 0 0 0-1.4 0zM6 17l3.5-3.5 2 2L8 19H6z" fill="#1a437f"/></svg></span>
                    </div>
                    <div class="section-head-line"></div>
                </div>
                @if(count($recommendationsLines))
                <ul class="bullets">@foreach($recommendationsLines as $line)<li>{{ $line }}</li>@endforeach</ul>
                @else<ul class="bullets"><li>—</li></ul>@endif
            </div>

        </td>
    </tr>
</table>
</div>

<div class="signs-wrap">
<table class="signs">
    <tr>
        <td>
            <div class="sig-head">
                <svg class="sig-ico" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.2"/><path d="M5.5 19c.8-3.2 3.3-5 6.5-5s5.7 1.8 6.5 5"/></svg>
                <div class="sig-ttl">توقيع المهندس المسؤول</div>
            </div>
            <div class="sig-line">الاسم : {{ $engineerName }}</div>
            <div class="sig-line">التوقيع :</div>
            <div class="sig-line">التاريخ : <span class="en">{{ $visitDateLabel }}</span></div>
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
            <div class="sig-line">الاسم : {{ $clientName !== '—' ? $clientName : '' }}</div>
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
    @if(!empty($waveSvg))
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
    @endif
</div>

</div>
