<div class="sheet">

<table class="header">
    <tr>
        <td class="brand">
            <table class="brand-inner">
                <tr>
                    <td class="logo-td">
                        @if(!empty($logoDataUri))
                            <img src="{{ $logoDataUri }}" alt="">
                        @endif
                    </td>
                    <td class="brand-text">
                        <div class="brand-name">{{ $companyNameAr }}</div>
                        <div class="contact-row"><img src="{{ $iconPhone }}" alt=""><span class="en">{{ $phone }}</span></div>
                        <div class="contact-row"><img src="{{ $iconEmail }}" alt=""><span class="en">{{ $email }}</span></div>
                        <div class="contact-row"><img src="{{ $iconLocation }}" alt=""><span>{{ $addressAr }}</span></div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="doc-cell">
            <div class="doc-title-wrap">
                <table class="doc-title-table">
                    <tr>
                        <td><div class="doc-title">تقرير زيارة موقع</div></td>
                        <td>
                            <div class="doc-ico">
                                <svg viewBox="0 0 24 24"><path d="M8 6h5l3 3v9H8V6zm5 0v3h3M9 12h6M9 15h4"/></svg>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <table class="meta">
                <colgroup><col><col></colgroup>
                <tr><td class="lab">رقم التقرير</td><td class="val"><span class="en">{{ $reportNo }}</span></td></tr>
                <tr><td class="lab">تاريخ الزيارة</td><td class="val"><span class="en">{{ $visitDateLabel }}</span></td></tr>
                <tr><td class="lab">وقت الزيارة</td><td class="val">{{ $visitTimeLabel }}</td></tr>
                <tr><td class="lab">نوع الزيارة</td><td class="val">{{ $visitTypeLabel }}</td></tr>
            </table>
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
            <table class="sig-head-table"><tr>
                <td><svg class="sig-ico" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.2"/><path d="M5.5 19c.8-3.2 3.3-5 6.5-5s5.7 1.8 6.5 5"/></svg></td>
                <td><div class="sig-ttl">توقيع المهندس المسؤول</div></td>
            </tr></table>
            <div class="sig-line">الاسم : {{ $engineerName }}</div>
            <div class="sig-line">التوقيع :</div>
            <div class="sig-line">التاريخ :</div>
        </td>
        <td>
            <table class="sig-head-table"><tr>
                <td><svg class="sig-ico" viewBox="0 0 24 24"><rect x="7" y="3.5" width="10" height="12" rx="1.2"/><path d="M9 7.5h6M9 10h6M9 12.5h4"/><path d="M8 16.5h8v2.2c0 .7-1.8 1.5-4 1.5s-4-.8-4-1.5V16.5z"/></svg></td>
                <td><div class="sig-ttl">ختم الشركة</div></td>
            </tr></table>
            <div class="stamp-box"></div>
        </td>
        <td>
            <table class="sig-head-table"><tr>
                <td><svg class="sig-ico" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.2"/><path d="M5.5 19c.8-3.2 3.3-5 6.5-5s5.7 1.8 6.5 5"/></svg></td>
                <td><div class="sig-ttl">توقيع واستلام المستلم</div></td>
            </tr></table>
            <div class="sig-line">الاسم :</div>
            <div class="sig-line">التوقيع :</div>
            <div class="sig-line">التاريخ :</div>
        </td>
    </tr>
</table>
</div>

<div class="footer">
    <table class="footer-bar">
        <tr>
            <td class="footer-side">
                @if(!empty($waveSvg))<img src="{{ $waveSvg }}" alt="">@endif
            </td>
            <td class="footer-center">
                <svg class="footer-bolt" viewBox="0 0 24 24"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                شكراً لتعاملكم معنا
                <svg class="footer-bolt" viewBox="0 0 24 24"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
            </td>
            <td class="footer-side">
                @if(!empty($waveSvg))<img src="{{ $waveSvg }}" alt="">@endif
            </td>
        </tr>
    </table>
</div>

</div>
