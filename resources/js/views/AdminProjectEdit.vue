<template>
  <div dir="rtl" class="w-full max-w-none">
    <div class="sf-page-header">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ isNew ? 'مشروع جديد' : (form.title_ar || 'تعديل المشروع') }}</h1>
        <router-link to="/admin/projects" class="text-sm text-gray-500 hover:text-blue-600">← العودة للمشاريع</router-link>
      </div>
      <button
        v-if="!isLocked"
        type="button"
        :disabled="submitting"
        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white px-5 py-2.5 rounded-lg text-sm font-medium"
        @click="handleSubmit"
      >
        {{ submitting ? 'جاري الحفظ...' : 'حفظ' }}
      </button>
    </div>

    <div v-if="isLocked" class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
      هذا المشروع <strong>مكتمل</strong> — العرض فقط، لا يمكن التعديل.
    </div>

    <div v-if="pageLoading" class="text-center py-16">
      <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <form v-else class="grid grid-cols-1 gap-4" @submit.prevent="handleSubmit">
      <!-- Basics -->
      <section class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100">
          <h2 class="font-bold text-gray-900 text-sm">بيانات المشروع</h2>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5 space-y-3">
        <div class="sf-form-grid">
          <div>
            <label class="sf-label">اسم المشروع (عربي) *</label>
            <input v-model="form.title_ar" type="text" class="sf-field" required :disabled="isLocked" />
          </div>
          <div>
            <label class="sf-label">Project name (EN) *</label>
            <input v-model="form.title" type="text" class="sf-field" required :disabled="isLocked" />
          </div>
        </div>
        <div class="sf-form-grid">
          <div>
            <label class="sf-label">الوصف (عربي) *</label>
            <textarea v-model="form.description_ar" rows="4" class="sf-field" required :disabled="isLocked"></textarea>
          </div>
          <div>
            <label class="sf-label">Description (EN) *</label>
            <textarea v-model="form.description" rows="4" class="sf-field" required :disabled="isLocked"></textarea>
          </div>
        </div>
        <div class="grid sm:grid-cols-3 gap-4">
          <div>
            <label class="sf-label">الحالة</label>
            <select v-model="form.status" class="sf-field" :disabled="isLocked">
              <option value="draft">مسودة</option>
              <option value="in_progress">قيد التنفيذ</option>
              <option value="on_hold">متوقف</option>
              <option value="completed">مكتمل</option>
              <option value="cancelled">ملغي</option>
            </select>
          </div>
          <div class="flex items-end">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="form.is_public" type="checkbox" class="w-4 h-4" :disabled="isLocked" />
              <span class="text-sm">عرض في الموقع (فرونت)</span>
            </label>
          </div>
          <div>
            <label class="sf-label">ترتيب العرض</label>
            <input v-model.number="form.order" type="number" min="0" class="sf-field" :disabled="isLocked" />
          </div>
        </div>
        </div>
      </section>

      <!-- Client -->
      <section class="sf-card border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100">
          <h2 class="font-bold text-gray-900 text-sm">العميل</h2>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5">
          <label class="block text-xs text-gray-500 mb-1">اختر العميل *</label>
          <div v-if="selectedCustomer" class="mb-2 p-2 bg-blue-50 rounded-lg border border-blue-100 flex items-center justify-between gap-2">
            <div>
              <span class="text-sm font-medium">{{ selectedCustomer.name }}</span>
              <span v-if="selectedCustomer.phone" class="text-xs text-gray-500 block">{{ selectedCustomer.phone }}</span>
            </div>
            <button v-if="!isLocked" type="button" class="text-xs text-red-600 shrink-0" @click="clearCustomer">إزالة</button>
          </div>
          <div v-if="!isLocked" class="relative" ref="customerWrap">
            <input
              v-model="customerQuery"
              type="text"
              class="sf-field"
              placeholder="ابحث عن عميل..."
              autocomplete="off"
              @focus="customerOpen = true"
              @keydown.escape="customerOpen = false"
            />
            <div
              v-if="customerOpen"
              class="absolute z-50 mt-1 w-full max-h-48 overflow-auto bg-white border rounded-xl shadow-lg"
            >
              <button
                v-for="c in filteredCustomers"
                :key="c.id"
                type="button"
                class="w-full text-right px-3 py-2 hover:bg-blue-50 border-b text-sm"
                @mousedown.prevent="selectCustomer(c)"
              >
                {{ c.name }} <span v-if="c.phone" class="text-gray-400">· {{ c.phone }}</span>
              </button>
              <p v-if="!filteredCustomers.length" class="p-3 text-sm text-gray-400 text-center">لا نتائج</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Location -->
      <section class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100">
          <h2 class="font-bold text-gray-900 text-sm">الموقع</h2>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5 space-y-3">
        <div>
          <label class="sf-label">عنوان / موقع المشروع</label>
          <input v-model="form.location" type="text" class="sf-field" placeholder="المدينة، المنطقة، تفاصيل..." :disabled="isLocked" @blur="syncMapsUrl" />
        </div>
        <div>
          <label class="sf-label">رابط الخريطة</label>
          <div class="flex gap-2">
            <input v-model="form.maps_url" type="url" class="sf-field flex-1" placeholder="Google Maps link" :disabled="isLocked" />
            <button type="button" class="shrink-0 px-3 py-2 border rounded-lg text-sm hover:bg-gray-50" :disabled="!mapsLink" @click="openMaps">فتح</button>
            <button type="button" class="shrink-0 px-3 py-2 border rounded-lg text-sm hover:bg-gray-50" :disabled="!mapsLink" @click="copyMaps">نسخ</button>
          </div>
        </div>
        </div>
      </section>

      <!-- Contacts + QR -->
      <section class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100 flex items-center justify-between gap-2">
          <h2 class="font-bold text-gray-900 text-sm">جهات الاتصال</h2>
          <button v-if="!isLocked" type="button" class="text-sm text-blue-600" @click="addContact">+ إضافة</button>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5 space-y-3">
          <div v-if="!contacts.length" class="text-sm text-gray-400">لا جهات اتصال بعد</div>
          <div v-for="(c, i) in contacts" :key="i" class="grid sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
            <input v-model="c.name" type="text" class="sf-field" placeholder="الاسم" :disabled="isLocked" />
            <input v-model="c.phone" type="text" class="sf-field" placeholder="الرقم" :disabled="isLocked" />
            <button v-if="!isLocked" type="button" class="text-red-500 px-2 py-2" @click="contacts.splice(i, 1)">×</button>
          </div>
        </div>
      </section>

      <section class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100">
          <h2 class="font-bold text-gray-900 text-sm">QR Code المشروع</h2>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5 flex flex-col items-center text-center gap-2">
          <img v-if="qrPath" :src="qrPath" alt="QR" class="w-36 h-36 border rounded-lg bg-white" />
          <div v-else class="w-36 h-36 border border-dashed rounded-lg bg-slate-50 flex items-center justify-center text-xs text-gray-400">
            يُنشأ بعد الحفظ
          </div>
          <p class="text-xs text-gray-500">اسم المشروع، العميل، الموقع، وجهات الاتصال</p>
        </div>
      </section>

      <!-- Public files -->
      <section class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100 flex items-center justify-between gap-2">
          <div>
            <h2 class="font-bold text-gray-900 text-sm">ملفات العرض (الموقع)</h2>
            <p class="text-[11px] text-gray-500">تظهر للزوار على الموقع</p>
          </div>
          <button
            v-if="!isLocked"
            type="button"
            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium"
            @click="openFilePicker('public')"
          >
            + إضافة ملف
          </button>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5">
          <div v-if="!visiblePublicFiles.length && !pendingPublic.length" class="rounded-xl border border-dashed border-gray-200 py-6 text-center text-gray-400 text-sm">
            لا ملفات عرض بعد
          </div>
          <div v-else class="flex flex-wrap gap-2">
            <div
              v-for="f in visiblePublicFiles"
              :key="'pf-' + f.id"
              class="w-[88px] rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm"
            >
              <div class="h-16 bg-slate-50 flex items-center justify-center relative">
                <img v-if="isImagePath(f.path)" :src="f.path" :alt="f.label" class="w-full h-full object-cover" />
                <div v-else class="flex flex-col items-center text-slate-400">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                  <span class="text-[9px] uppercase">{{ fileExt(f.path) }}</span>
                </div>
              </div>
              <p class="px-1.5 py-1 text-[10px] font-medium text-gray-800 truncate text-center" :title="f.label">{{ f.label }}</p>
              <div class="flex border-t border-gray-100">
                <a :href="f.path" target="_blank" download class="flex-1 flex items-center justify-center py-1.5 text-blue-600 hover:bg-blue-50" title="تحميل / فتح">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </a>
                <button
                  v-if="!isLocked"
                  type="button"
                  class="flex-1 flex items-center justify-center py-1.5 text-red-500 hover:bg-red-50 border-r border-gray-100"
                  title="حذف"
                  @click="toggleKeepFile(f.id)"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </div>
            <div
              v-for="(row, i) in pendingPublic"
              :key="'pp-' + i"
              class="w-[88px] rounded-lg border border-blue-200 bg-blue-50/30 overflow-hidden shadow-sm"
            >
              <div class="h-16 bg-white flex items-center justify-center">
                <img v-if="row.preview" :src="row.preview" :alt="row.label" class="w-full h-full object-cover" />
                <div v-else class="flex flex-col items-center text-slate-400">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                  <span class="text-[9px]">جديد</span>
                </div>
              </div>
              <p class="px-1.5 py-1 text-[10px] font-medium text-gray-800 truncate text-center">{{ row.label || row.file?.name || 'ملف' }}</p>
              <div class="flex border-t border-blue-100">
                <button type="button" class="flex-1 flex items-center justify-center py-1.5 text-red-500 hover:bg-red-50" title="حذف" @click="removePending('public', i)">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Private files -->
      <section class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100 flex items-center justify-between gap-2">
          <div>
            <h2 class="font-bold text-gray-900 text-sm">ملفات خاصة (إدارة)</h2>
            <p class="text-[11px] text-gray-500">لا تظهر للعميل</p>
          </div>
          <button
            v-if="!isLocked"
            type="button"
            class="bg-slate-800 hover:bg-slate-900 text-white px-3 py-1.5 rounded-lg text-xs font-medium"
            @click="openFilePicker('private')"
          >
            + إضافة ملف
          </button>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5">
          <div v-if="!visiblePrivateFiles.length && !pendingPrivate.length" class="rounded-xl border border-dashed border-gray-200 py-6 text-center text-gray-400 text-sm">
            لا ملفات خاصة بعد
          </div>
          <div v-else class="flex flex-wrap gap-2">
            <div
              v-for="f in visiblePrivateFiles"
              :key="'prf-' + f.id"
              class="w-[88px] rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm"
            >
              <div class="h-16 bg-slate-50 flex items-center justify-center">
                <img v-if="isImagePath(f.path)" :src="f.path" :alt="f.label" class="w-full h-full object-cover" />
                <div v-else class="flex flex-col items-center text-slate-400">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                  <span class="text-[9px] uppercase">{{ fileExt(f.path) }}</span>
                </div>
              </div>
              <p class="px-1.5 py-1 text-[10px] font-medium text-gray-800 truncate text-center" :title="f.label">{{ f.label }}</p>
              <div class="flex border-t border-gray-100">
                <a :href="f.path" target="_blank" download class="flex-1 flex items-center justify-center py-1.5 text-blue-600 hover:bg-blue-50" title="تحميل / فتح">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </a>
                <button
                  v-if="!isLocked"
                  type="button"
                  class="flex-1 flex items-center justify-center py-1.5 text-red-500 hover:bg-red-50 border-r border-gray-100"
                  title="حذف"
                  @click="toggleKeepFile(f.id)"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </div>
            <div
              v-for="(row, i) in pendingPrivate"
              :key="'prp-' + i"
              class="w-[88px] rounded-lg border border-slate-300 bg-slate-50 overflow-hidden shadow-sm"
            >
              <div class="h-16 bg-white flex items-center justify-center">
                <img v-if="row.preview" :src="row.preview" :alt="row.label" class="w-full h-full object-cover" />
                <div v-else class="flex flex-col items-center text-slate-400">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                  <span class="text-[9px]">جديد</span>
                </div>
              </div>
              <p class="px-1.5 py-1 text-[10px] font-medium text-gray-800 truncate text-center">{{ row.label || row.file?.name || 'ملف' }}</p>
              <div class="flex border-t border-slate-200">
                <button type="button" class="flex-1 flex items-center justify-center py-1.5 text-red-500 hover:bg-red-50" title="حذف" @click="removePending('private', i)">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Finance summary -->
      <section v-if="!isNew" class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100">
          <h2 class="font-bold text-gray-900 text-sm">الميزانية والربح</h2>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5 space-y-3">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <div class="rounded-xl bg-blue-50 border border-blue-100 p-4">
            <p class="text-xs text-blue-700 mb-1">قيمة المشروع (الفاتورة)</p>
            <p class="text-xl font-bold text-blue-900">{{ money(finance.contract_value) }}</p>
          </div>
          <div class="rounded-xl bg-rose-50 border border-rose-100 p-4">
            <p class="text-xs text-rose-700 mb-1">المصاريف</p>
            <p class="text-xl font-bold text-rose-900">− {{ money(finance.expenses_total) }}</p>
          </div>
          <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4">
            <p class="text-xs text-emerald-700 mb-1">الربح</p>
            <p class="text-xl font-bold text-emerald-900">{{ money(finance.profit) }}</p>
          </div>
          <div class="rounded-xl bg-amber-50 border border-amber-100 p-4">
            <p class="text-xs text-amber-700 mb-1">دفعات العميل / المتبقي</p>
            <p class="text-lg font-bold text-amber-900">{{ money(finance.payments_total) }}</p>
            <p class="text-xs text-amber-700 mt-1">متبقي: {{ money(finance.balance_due) }}</p>
          </div>
        </div>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between border-b pb-2"><span>قيمة الفاتورة</span><span class="font-medium">{{ money(finance.contract_value) }}</span></div>
          <div class="flex justify-between border-b pb-2 text-rose-700"><span>− المصاريف</span><span>{{ money(finance.expenses_total) }}</span></div>
          <div class="flex justify-between font-bold text-emerald-700"><span>= الربح</span><span>{{ money(finance.profit) }}</span></div>
          <div class="flex justify-between border-b pb-2 text-slate-600"><span>رأس مال المشروع (= الفاتورة)</span><span>{{ money(finance.capital_total) }}</span></div>
          <div class="flex justify-between border-b pb-2 text-indigo-700"><span>− حصص الشركاء</span><span>{{ money(finance.shares_total) }}</span></div>
          <div class="flex justify-between font-bold text-slate-800"><span>= ربح الشركة</span><span>{{ money(finance.company_profit) }}</span></div>
          <div class="flex justify-between border-t pt-2 text-amber-800"><span>مدفوع من العميل</span><span>{{ money(finance.payments_total) }}</span></div>
        </div>
        </div>
      </section>

      <!-- Payments -->
      <section v-if="!isNew" class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100">
          <h2 class="font-bold text-gray-900 text-sm">دفعات العميل</h2>
          <p class="text-[11px] text-gray-500">المبالغ المستلمة من العميل</p>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5 space-y-3">
          <div v-if="!isLocked" class="flex justify-end">
            <button type="button" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium" @click="openPaymentForm">+ دفعة جديدة</button>
          </div>
        <div v-if="!payments.length" class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-gray-400 text-sm">
          لا دفعات بعد
        </div>
        <div v-else class="overflow-x-auto rounded-xl border border-gray-200">
          <table class="w-full text-sm min-w-[560px]">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-4 py-3 text-right font-medium text-gray-500">التاريخ</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">المبلغ</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">النوع</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">الوصل</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">ملاحظة</th>
                <th class="px-4 py-3 text-center font-medium text-gray-500">إجراءات</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="p in payments" :key="p.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-700">{{ p.paid_at }}</td>
                <td class="px-4 py-3 font-semibold text-amber-800">{{ money(p.amount) }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex rounded-full bg-amber-50 text-amber-800 px-2.5 py-0.5 text-xs font-medium">
                    {{ paymentTypeLabel(p.payment_type) }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <a v-if="p.receipt_path" :href="p.receipt_path" target="_blank" class="text-blue-600 text-xs font-medium hover:underline">عرض الوصل</a>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs max-w-[160px] truncate">{{ p.notes || '—' }}</td>
                <td class="px-4 py-3 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button type="button" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="عرض في المتصفح" @click="viewPaymentPdf(p)">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    <button type="button" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50" title="تصدير PDF" @click="downloadPaymentPdf(p)">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    </button>
                    <button v-if="!isLocked" type="button" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="حذف" @click="removePayment(p.id)">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-amber-50/60 border-t">
              <tr>
                <td class="px-4 py-3 font-medium text-amber-900" colspan="1">المجموع</td>
                <td class="px-4 py-3 font-bold text-amber-900">{{ money(finance.payments_total) }}</td>
                <td colspan="4" class="px-4 py-3 text-xs text-amber-800">متبقي على العميل: {{ money(finance.balance_due) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
        </div>
      </section>

      <!-- Expenses -->
      <section v-if="!isNew" class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100">
          <h2 class="font-bold text-gray-900 text-sm">المصاريف</h2>
          <p class="text-[11px] text-gray-500">تُخصم من قيمة المشروع للربح</p>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5 space-y-3">
          <div v-if="!isLocked" class="flex justify-end">
            <button type="button" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium" @click="openExpenseForm">+ مصروف جديد</button>
          </div>
        <div v-if="!expenses.length" class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-gray-400 text-sm">
          لا مصاريف بعد
        </div>
        <div v-else class="overflow-x-auto rounded-xl border border-gray-200">
          <table class="w-full text-sm min-w-[480px]">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-4 py-3 text-right font-medium text-gray-500">الاسم</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">القيمة</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">التاريخ</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">الفاتورة</th>
                <th class="px-4 py-3 text-center font-medium text-gray-500">إجراءات</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="e in expenses" :key="e.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">{{ e.name }}</td>
                <td class="px-4 py-3 font-semibold text-rose-700">{{ money(e.amount) }}</td>
                <td class="px-4 py-3 text-gray-600">{{ e.spent_at || '—' }}</td>
                <td class="px-4 py-3">
                  <a v-if="e.receipt_path" :href="e.receipt_path" target="_blank" class="text-blue-600 text-xs font-medium hover:underline">عرض الفاتورة</a>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <button v-if="!isLocked" type="button" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="حذف" @click="removeExpense(e.id)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-rose-50/60 border-t">
              <tr>
                <td class="px-4 py-3 font-medium text-rose-900">مجموع المصاريف</td>
                <td class="px-4 py-3 font-bold text-rose-900">{{ money(finance.expenses_total) }}</td>
                <td colspan="3"></td>
              </tr>
            </tfoot>
          </table>
        </div>
        </div>
      </section>

      <!-- Profit shares -->
      <section v-if="!isNew" class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100">
          <h2 class="font-bold text-gray-900 text-sm">توزيع الربح / الشركاء</h2>
          <p class="text-[11px] text-gray-500">رأس المال = الفاتورة · الحصة = مساهمة ÷ فاتورة × ربح</p>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5 space-y-3">
          <div v-if="!isLocked" class="flex justify-end">
            <button type="button" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium" @click="openShareForm">+ شريك جديد</button>
          </div>

        <div class="grid sm:grid-cols-3 gap-3 text-sm">
          <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3">
            <p class="text-xs text-emerald-700 mb-0.5">ربح المشروع</p>
            <p class="font-bold text-emerald-900">{{ money(finance.profit) }}</p>
          </div>
          <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3">
            <p class="text-xs text-slate-500 mb-0.5">رأس مال المشروع (= الفاتورة)</p>
            <p class="font-bold text-slate-800">{{ money(finance.capital_total) }}</p>
          </div>
          <div class="rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-3">
            <p class="text-xs text-indigo-700 mb-0.5">ربح الشركة بعد الحصص</p>
            <p class="font-bold text-indigo-900">{{ money(finance.company_profit) }}</p>
          </div>
        </div>

        <div v-if="!profitShares.length" class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-gray-400 text-sm">
          لا شركاء بعد — أضف شريك بمساهمة من رأس المال أو بنسبة
        </div>
        <div v-else class="overflow-x-auto rounded-xl border border-gray-200">
          <table class="w-full text-sm min-w-[720px]">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-4 py-3 text-right font-medium text-gray-500">الاسم</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">النوع</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">القيمة المدخلة</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">نسبته من رأس المال</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">حصته من الربح</th>
                <th class="px-4 py-3 text-center font-medium text-gray-500">إجراءات</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="s in profitShares" :key="s.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">{{ s.name }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="s.share_type === 'percent' ? 'bg-violet-50 text-violet-800' : 'bg-sky-50 text-sky-800'"
                  >
                    {{ s.share_type === 'percent' ? 'نسبة من الربح' : 'رأس مال' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-800">
                  <span v-if="s.share_type === 'percent'">{{ Number(s.value).toLocaleString() }}%</span>
                  <span v-else>{{ money(s.value) }}</span>
                </td>
                <td class="px-4 py-3 text-gray-600">
                  <template v-if="s.share_type === 'capital' && s.capital_percent != null">
                    <span class="font-semibold text-sky-800">{{ s.capital_percent }}%</span>
                    <span class="text-xs text-gray-400 block">{{ money(s.value) }} ÷ {{ money(finance.capital_total) }}</span>
                  </template>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 font-bold text-indigo-700">{{ money(s.calculated_amount) }}</td>
                <td class="px-4 py-3 text-center">
                  <button v-if="!isLocked" type="button" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="حذف" @click="removeShare(s.id)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
            <tfoot class="border-t">
              <tr class="bg-indigo-50/70">
                <td class="px-4 py-3 font-medium text-indigo-900" colspan="4">مجموع حصص الشركاء</td>
                <td class="px-4 py-3 font-bold text-indigo-900">{{ money(finance.shares_total) }}</td>
                <td></td>
              </tr>
              <tr class="bg-emerald-50">
                <td class="px-4 py-3 font-bold text-emerald-900" colspan="4">ربح الشركة المتبقي</td>
                <td class="px-4 py-3 font-bold text-emerald-900">{{ money(finance.company_profit) }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
        </div>
      </section>

      <!-- Delivery notes -->
      <section v-if="!isNew" class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100">
          <h2 class="font-bold text-gray-900 text-sm">Delivery Notes</h2>
          <p class="text-[11px] text-gray-500">إشعارات التسليم · PDF للعميل</p>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5 space-y-3">
          <div v-if="!isLocked" class="flex justify-end">
            <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium" @click="openDnCreate">+ Delivery Note</button>
          </div>

        <div v-if="!deliveryNotes.length" class="rounded-xl border border-dashed border-gray-200 py-12 text-center text-gray-400 text-sm">
          لا إشعارات تسليم بعد — أنشئ أول وصل للعميل
        </div>

        <div v-else class="overflow-x-auto rounded-xl border border-gray-200">
          <table class="w-full text-sm min-w-[720px]">
            <thead class="bg-slate-50 text-gray-500 border-b">
              <tr>
                <th class="px-4 py-3 text-right font-medium">الرقم</th>
                <th class="px-4 py-3 text-right font-medium">العنوان</th>
                <th class="px-4 py-3 text-right font-medium">التاريخ</th>
                <th class="px-4 py-3 text-center font-medium">البنود</th>
                <th class="px-4 py-3 text-right font-medium">استلم / سلّم</th>
                <th class="px-4 py-3 text-center font-medium">إجراءات</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="dn in deliveryNotes" :key="dn.id" class="hover:bg-slate-50/80">
                <td class="px-4 py-3 font-mono text-xs font-semibold text-blue-800">{{ dn.number }}</td>
                <td class="px-4 py-3">
                  <p class="font-medium text-gray-900">{{ dn.title || 'Delivery Note' }}</p>
                  <p v-if="dn.notes" class="text-xs text-gray-400 line-clamp-1 mt-0.5">{{ dn.notes }}</p>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ formatDnDate(dn.delivered_at) }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex min-w-[1.75rem] justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium">
                    {{ (dn.items || []).length }}
                  </span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">
                  <div>{{ dn.received_by || '—' }}</div>
                  <div class="text-gray-400">{{ dn.delivered_by || '—' }}</div>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-1.5">
                    <button type="button" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="عرض" @click="openDnView(dn)">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    <button type="button" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50" title="PDF للعميل" @click="downloadDnPdf(dn)">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                    </button>
                    <button v-if="!isLocked" type="button" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="حذف" @click="removeDeliveryNote(dn.id)">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        </div>
      </section>

      <!-- Linked docs -->
      <section v-if="!isNew" class="sf-card overflow-hidden border border-gray-100">
        <div class="px-4 py-3 sm:px-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2">
          <div>
            <h2 class="font-bold text-gray-900 text-sm">العروض والفواتير المرتبطة</h2>
            <p class="text-[11px] text-gray-500">فاتورة واحدة وعرض سعر واحد للمشروع</p>
          </div>
          <div v-if="!isLocked" class="flex flex-wrap gap-2">
            <button
              type="button"
              class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium disabled:opacity-60"
              :disabled="!form.customer_id"
              @click="openLinkInvoiceModal"
            >
              {{ primaryInvoice ? 'تغيير الفاتورة' : 'ربط فاتورة' }}
            </button>
            <button
              type="button"
              class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium disabled:opacity-60"
              :disabled="!form.customer_id"
              @click="openLinkQuotationModal"
            >
              {{ primaryQuotation ? 'تغيير عرض السعر' : 'ربط عرض سعر' }}
            </button>
          </div>
        </div>
        <div class="px-4 py-4 sm:px-5 sm:py-5 space-y-4">
          <p v-if="!form.customer_id" class="text-sm text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
            اختر العميل أولاً لتتمكن من ربط الفاتورة أو عرض السعر.
          </p>

          <div class="grid sm:grid-cols-2 gap-3">
            <div class="rounded-xl border p-4" :class="primaryInvoice ? 'border-amber-200 bg-amber-50/40' : 'border-dashed border-gray-200'">
              <p class="text-xs font-semibold text-gray-500 mb-2">فاتورة المشروع</p>
              <template v-if="primaryInvoice">
                <p class="font-mono font-bold text-amber-900">{{ primaryInvoice.number }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ money(Number(primaryInvoice.total || primaryInvoice.amount || 0)) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ formatDnDate(primaryInvoice.date) }}</p>
                <router-link :to="`/admin/invoices/${primaryInvoice.id}`" class="inline-block text-xs text-blue-600 mt-2 hover:underline">فتح الفاتورة</router-link>
              </template>
              <p v-else class="text-sm text-gray-400">لا فاتورة مرتبطة</p>
            </div>
            <div class="rounded-xl border p-4" :class="primaryQuotation ? 'border-blue-200 bg-blue-50/40' : 'border-dashed border-gray-200'">
              <p class="text-xs font-semibold text-gray-500 mb-2">عرض السعر</p>
              <template v-if="primaryQuotation">
                <p class="font-mono font-bold text-blue-900">{{ primaryQuotation.number }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ money(Number(primaryQuotation.total || 0)) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ formatDnDate(primaryQuotation.date) }}</p>
                <router-link :to="`/admin/quotations/${primaryQuotation.id}`" class="inline-block text-xs text-blue-600 mt-2 hover:underline">فتح العرض</router-link>
              </template>
              <p v-else class="text-sm text-gray-400">لا عرض مرتبط</p>
            </div>
          </div>
        </div>
      </section>
    </form>

    <!-- Link invoice modal -->
    <Teleport to="body">
      <div v-if="showLinkInvoiceModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-6" dir="rtl" @click.self="showLinkInvoiceModal = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="px-5 py-4 border-b bg-amber-50">
            <h3 class="text-lg font-bold text-slate-900">ربط فاتورة بالمشروع</h3>
            <p class="text-xs text-slate-500 mt-1">اختر فاتورة من فواتير العميل — فاتورة واحدة فقط</p>
          </div>
          <div class="p-5 space-y-4">
            <div>
              <label class="sf-label">فاتورة العميل *</label>
              <select v-model="selectedLinkInvoiceId" class="sf-field">
                <option :value="null">— اختر فاتورة —</option>
                <option v-for="inv in linkOptionsInvoices" :key="inv.id" :value="inv.id">
                  {{ invoiceOptionLabel(inv) }}
                </option>
              </select>
            </div>
            <p v-if="!linkOptionsInvoices.length" class="text-sm text-gray-400 text-center py-4">لا فواتير لهذا العميل</p>
            <p v-if="selectedLinkInvoiceId" class="text-xs text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2">
              سيتم ربط عرض السعر التابع للفاتورة تلقائياً.
            </p>
          </div>
          <div class="px-5 py-4 border-t flex gap-3">
            <button type="button" class="flex-1 border py-2.5 rounded-lg text-sm" @click="showLinkInvoiceModal = false">إلغاء</button>
            <button type="button" class="flex-1 bg-amber-500 text-white py-2.5 rounded-lg text-sm font-medium disabled:opacity-60" :disabled="linkSaving || !selectedLinkInvoiceId" @click="saveLinkInvoice">
              {{ linkSaving ? 'جاري الربط...' : 'ربط الفاتورة' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Link quotation modal -->
    <Teleport to="body">
      <div v-if="showLinkQuotationModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-6" dir="rtl" @click.self="showLinkQuotationModal = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="px-5 py-4 border-b bg-blue-50">
            <h3 class="text-lg font-bold text-slate-900">ربط عرض سعر بالمشروع</h3>
            <p class="text-xs text-slate-500 mt-1">اختر عرضاً من عروض العميل — عرض واحد فقط</p>
          </div>
          <div class="p-5 space-y-4">
            <div>
              <label class="sf-label">عرض السعر *</label>
              <select v-model="selectedLinkQuotationId" class="sf-field">
                <option :value="null">— اختر عرض سعر —</option>
                <option v-for="q in linkOptionsQuotations" :key="q.id" :value="q.id">
                  {{ quotationOptionLabel(q) }}
                </option>
              </select>
            </div>
            <p v-if="!linkOptionsQuotations.length" class="text-sm text-gray-400 text-center py-4">لا عروض لهذا العميل</p>
          </div>
          <div class="px-5 py-4 border-t flex gap-3">
            <button type="button" class="flex-1 border py-2.5 rounded-lg text-sm" @click="showLinkQuotationModal = false">إلغاء</button>
            <button type="button" class="flex-1 bg-blue-600 text-white py-2.5 rounded-lg text-sm font-medium disabled:opacity-60" :disabled="linkSaving || !selectedLinkQuotationId" @click="saveLinkQuotation">
              {{ linkSaving ? 'جاري الربط...' : 'ربط العرض' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- File picker modal -->
    <Teleport to="body">
      <div v-if="showFilePicker" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-6" dir="rtl" @click.self="closeFilePicker">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[95vh] overflow-hidden flex flex-col">
          <div class="px-5 py-4 border-b bg-slate-50 flex items-center justify-between gap-3">
            <div>
              <h3 class="text-lg font-bold text-slate-900">
                {{ filePickerKind === 'public' ? 'إضافة ملف عرض' : 'إضافة ملف خاص' }}
              </h3>
              <p class="text-xs text-slate-500">اختر صورة أو ملف ثم سمِّه بوضوح</p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-700 text-2xl leading-none" @click="closeFilePicker">×</button>
          </div>
          <div class="p-5 space-y-4 flex-1 overflow-y-auto">
            <div>
              <label class="sf-label">اسم الملف *</label>
              <input
                v-model="filePickerLabel"
                type="text"
                class="sf-field"
                :placeholder="filePickerKind === 'public' ? 'مثال: صورة الواجهة' : 'مثال: عرض السعر الموقّع'"
              />
            </div>
            <div>
              <label class="sf-label">اختر الملف *</label>
              <input
                ref="filePickerInput"
                type="file"
                accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.zip"
                class="sf-field"
                @change="onFilePickerChange"
              />
            </div>
            <div v-if="filePickerPreview || filePickerFile" class="rounded-xl border border-slate-200 overflow-hidden bg-slate-50">
              <img v-if="filePickerPreview" :src="filePickerPreview" alt="preview" class="w-full max-h-48 object-contain bg-white" />
              <div v-else class="p-4 text-sm text-slate-600 flex items-center gap-2">
                <svg class="w-6 h-6 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span class="truncate">{{ filePickerFile?.name }}</span>
              </div>
            </div>
          </div>
          <div class="px-5 py-4 border-t flex gap-3">
            <button type="button" class="flex-1 border border-slate-300 py-2.5 rounded-lg text-sm" @click="closeFilePicker">إلغاء</button>
            <button type="button" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-medium" @click="confirmFilePicker">
              إضافة
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Create Payment modal -->
    <Teleport to="body">
      <div v-if="showPaymentForm" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-6" dir="rtl" @click.self="showPaymentForm = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[95vh] overflow-hidden flex flex-col">
          <div class="px-5 sm:px-6 py-4 border-b bg-amber-50 flex items-center justify-between gap-3">
            <div>
              <h3 class="text-lg font-bold text-slate-900">دفعة عميل جديدة</h3>
              <p class="text-xs text-slate-500">تسجيل دفعة مستلمة من العميل</p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-700 text-2xl leading-none" @click="showPaymentForm = false">×</button>
          </div>
          <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <div>
              <label class="sf-label">المبلغ *</label>
              <input v-model.number="paymentForm.amount" type="number" min="0.01" step="0.01" class="sf-field" placeholder="0.00" />
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="sf-label">نوع الدفع</label>
                <select v-model="paymentForm.payment_type" class="sf-field">
                  <option value="cash">كاش</option>
                  <option value="bank">تحويل بنكي</option>
                  <option value="card">بطاقة</option>
                  <option value="transfer">حوالة</option>
                  <option value="cheque">شيك</option>
                  <option value="other">أخرى</option>
                </select>
              </div>
              <div>
                <label class="sf-label">التاريخ</label>
                <input v-model="paymentForm.paid_at" type="date" class="sf-field" />
              </div>
            </div>
            <div>
              <label class="sf-label">صورة الوصل</label>
              <input type="file" accept="image/*,application/pdf" class="sf-field" @change="onReceiptFile" />
            </div>
            <div>
              <label class="sf-label">ملاحظة</label>
              <input v-model="paymentForm.notes" type="text" class="sf-field" placeholder="اختياري" />
            </div>
          </div>
          <div class="px-5 sm:px-6 py-4 border-t bg-white flex gap-3">
            <button type="button" class="flex-1 border border-slate-300 py-2.5 rounded-lg text-sm" @click="showPaymentForm = false">إلغاء</button>
            <button type="button" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-lg text-sm font-medium disabled:opacity-60" :disabled="paymentSaving" @click="addPayment">
              {{ paymentSaving ? 'جاري الحفظ...' : 'حفظ الدفعة' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Create Expense modal -->
    <Teleport to="body">
      <div v-if="showExpenseForm" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-6" dir="rtl" @click.self="showExpenseForm = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[95vh] overflow-hidden flex flex-col">
          <div class="px-5 sm:px-6 py-4 border-b bg-rose-50 flex items-center justify-between gap-3">
            <div>
              <h3 class="text-lg font-bold text-slate-900">مصروف جديد</h3>
              <p class="text-xs text-slate-500">يُخصم من ربح المشروع تلقائياً</p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-700 text-2xl leading-none" @click="showExpenseForm = false">×</button>
          </div>
          <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <div>
              <label class="sf-label">اسم المصروف *</label>
              <input v-model="expenseForm.name" list="expense-presets-modal" type="text" class="sf-field" placeholder="وقود / عمال / كاميرات..." />
              <datalist id="expense-presets-modal">
                <option v-for="n in expensePresets" :key="n" :value="n" />
              </datalist>
            </div>
            <div>
              <label class="sf-label">القيمة *</label>
              <input v-model.number="expenseForm.amount" type="number" min="0.01" step="0.01" class="sf-field" placeholder="0.00" />
            </div>
            <div>
              <label class="sf-label">فاتورة المصروف</label>
              <input type="file" accept="image/*,application/pdf" class="sf-field" @change="onExpenseReceiptFile" />
              <p v-if="expenseForm.receipt" class="text-xs text-emerald-700 mt-1">تم اختيار: {{ expenseForm.receipt.name }}</p>
            </div>
          </div>
          <div class="px-5 sm:px-6 py-4 border-t bg-white flex gap-3">
            <button type="button" class="flex-1 border border-slate-300 py-2.5 rounded-lg text-sm" @click="showExpenseForm = false">إلغاء</button>
            <button type="button" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-2.5 rounded-lg text-sm font-medium disabled:opacity-60" :disabled="expenseSaving" @click="addExpense">
              {{ expenseSaving ? 'جاري الحفظ...' : 'حفظ المصروف' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Create Profit Share modal -->
    <Teleport to="body">
      <div v-if="showShareForm" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-6" dir="rtl" @click.self="showShareForm = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[95vh] overflow-hidden flex flex-col">
          <div class="px-5 sm:px-6 py-4 border-b bg-indigo-50 flex items-center justify-between gap-3">
            <div>
              <h3 class="text-lg font-bold text-slate-900">إضافة شريك</h3>
              <p class="text-xs text-slate-500">نسبة من الربح أو مساهمة رأس مال</p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-700 text-2xl leading-none" @click="showShareForm = false">×</button>
          </div>
          <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <div>
              <label class="sf-label">الاسم *</label>
              <input v-model="shareForm.name" type="text" class="sf-field" placeholder="اسم الشريك" />
            </div>
            <div>
              <label class="sf-label">نوع الحصة *</label>
              <select v-model="shareForm.share_type" class="sf-field">
                <option value="capital">رأس مال (مساهمة من قيمة الفاتورة)</option>
                <option value="percent">نسبة ثابتة من ربح المشروع</option>
              </select>
            </div>
            <div>
              <label class="sf-label">{{ shareForm.share_type === 'percent' ? 'النسبة من الربح %' : 'كم دفع من رأس المال AED' }} *</label>
              <input
                v-model.number="shareForm.value"
                type="number"
                min="0.01"
                :max="shareForm.share_type === 'percent' ? 100 : (finance.capital_total || undefined)"
                step="0.01"
                class="sf-field"
                :placeholder="shareForm.share_type === 'percent' ? 'مثلاً 5' : 'مثلاً 3000'"
              />
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 text-xs text-slate-600 leading-relaxed">
              <template v-if="shareForm.share_type === 'capital'">
                رأس مال المشروع = قيمة الفاتورة تلقائياً:
                <strong>{{ money(finance.capital_total) }}</strong>.
                لو دفع 3000 → نسبته =
                {{ finance.capital_total ? ((3000 / finance.capital_total) * 100).toFixed(1) : '—' }}%
                من الربح (<strong>{{ money(finance.profit) }}</strong>).
              </template>
              <template v-else>
                النسبة تُطبَّق مباشرة على ربح المشروع الحالي:
                <strong>{{ money(finance.profit) }}</strong>
              </template>
            </div>
          </div>
          <div class="px-5 sm:px-6 py-4 border-t bg-white flex gap-3">
            <button type="button" class="flex-1 border border-slate-300 py-2.5 rounded-lg text-sm" @click="showShareForm = false">إلغاء</button>
            <button type="button" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium disabled:opacity-60" :disabled="shareSaving" @click="addShare">
              {{ shareSaving ? 'جاري الحفظ...' : 'حفظ الشريك' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Create Delivery Note modal -->
    <Teleport to="body">
      <div v-if="showDnForm" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-6" dir="rtl" @click.self="showDnForm = false; dnItemOpen = null">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[95vh] overflow-hidden flex flex-col">
          <div class="px-5 sm:px-6 py-4 border-b bg-slate-50 flex items-center justify-between gap-3">
            <div>
              <h3 class="text-lg font-bold text-slate-900">Delivery Note جديد</h3>
              <p class="text-xs text-slate-500">سيتم تجهيز وصل يمكن تسليمه للعميل وتصديره PDF</p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-700 text-2xl leading-none" @click="showDnForm = false">×</button>
          </div>
          <div class="p-5 sm:p-6 overflow-y-auto space-y-5 flex-1">
            <div class="grid sm:grid-cols-2 gap-4">
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] uppercase tracking-wide text-slate-500 mb-1">العميل</p>
                <p class="font-semibold text-slate-900">{{ selectedCustomer?.name || '—' }}</p>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] uppercase tracking-wide text-slate-500 mb-1">المشروع</p>
                <p class="font-semibold text-slate-900">{{ form.title_ar || form.title }}</p>
              </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="sf-label">عنوان التسليم *</label>
                <input v-model="dnForm.title" type="text" class="sf-field" placeholder="مثال: تسليم الكاميرات والجهاز" />
              </div>
              <div>
                <label class="sf-label">تاريخ التسليم</label>
                <input v-model="dnForm.delivered_at" type="date" class="sf-field" />
              </div>
              <div>
                <label class="sf-label">سلّم (Delivered By)</label>
                <input v-model="dnForm.delivered_by" type="text" class="sf-field" placeholder="اسم الفني / الشركة" />
              </div>
              <div>
                <label class="sf-label">استلم (Received By)</label>
                <input v-model="dnForm.received_by" type="text" class="sf-field" placeholder="اسم المستلم عند العميل" />
              </div>
            </div>
            <div>
              <label class="sf-label">ملاحظات</label>
              <textarea v-model="dnForm.notes" rows="3" class="sf-field" placeholder="حالة التغليف، موقع التسليم، ملاحظات للعميل..."></textarea>
            </div>
            <div>
              <div class="flex items-center justify-between mb-2">
                <div>
                  <label class="sf-label !mb-0">البنود المسلّمة</label>
                  <p class="text-[11px] text-gray-500 mt-0.5">اختر من منتجات الفاتورة المرتبطة بالمشروع</p>
                </div>
                <button type="button" class="text-sm text-blue-600 font-medium" @click="addDnItemRow">+ بند</button>
              </div>
              <div v-if="!invoiceLineItems.length" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 mb-3">
                لا توجد بنود فاتورة لهذا المشروع بعد — أنشئ فاتورة أولاً لتختار منها المنتجات.
              </div>
              <div class="rounded-xl border border-slate-200 overflow-visible">
                <table class="w-full text-sm">
                  <thead class="bg-slate-100 text-slate-600">
                    <tr>
                      <th class="px-3 py-2 text-right font-medium">#</th>
                      <th class="px-3 py-2 text-right font-medium">المنتج (من الفاتورة)</th>
                      <th class="px-3 py-2 text-right font-medium w-28">الكمية</th>
                      <th class="px-3 py-2 text-right font-medium w-24">الوحدة</th>
                      <th class="px-3 py-2 w-10"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, i) in dnForm.items" :key="'dn-new-' + i" class="border-t border-slate-100 align-top">
                      <td class="px-3 py-2 text-slate-400 pt-3">{{ i + 1 }}</td>
                      <td class="px-3 py-2">
                        <div class="relative">
                          <input
                            v-model="row.search"
                            type="text"
                            class="sf-field !py-1.5"
                            placeholder="ابحث واختر من بنود الفاتورة..."
                            :disabled="!invoiceLineItems.length"
                            @focus="dnItemOpen = i"
                            @keydown.escape="dnItemOpen = null"
                          />
                          <div
                            v-if="dnItemOpen === i && invoiceLineItems.length"
                            class="absolute z-40 mt-1 w-full max-h-48 overflow-auto bg-white border rounded-xl shadow-lg"
                          >
                            <button
                              v-for="p in filteredDnProducts(row.search)"
                              :key="p.key"
                              type="button"
                              class="w-full text-right px-3 py-2 hover:bg-blue-50 border-b text-sm"
                              @click="pickDnProduct(i, p)"
                            >
                              <span class="font-medium text-slate-800">{{ p.description }}</span>
                              <span class="block text-[11px] text-slate-400">
                                فاتورة {{ p.invoice_number }} · الكمية {{ p.quantity }}
                                <template v-if="p.code"> · {{ p.code }}</template>
                              </span>
                            </button>
                            <p v-if="!filteredDnProducts(row.search).length" class="p-3 text-sm text-gray-400 text-center">لا نتائج</p>
                          </div>
                          <p v-if="row.description" class="text-[11px] text-emerald-700 mt-1 truncate">المختار: {{ row.description }}</p>
                        </div>
                      </td>
                      <td class="px-3 py-2">
                        <input v-model.number="row.quantity" type="number" min="0.01" step="0.01" class="sf-field !py-1.5" />
                      </td>
                      <td class="px-3 py-2">
                        <input v-model="row.unit" type="text" class="sf-field !py-1.5" placeholder="pcs" />
                      </td>
                      <td class="px-3 py-2">
                        <button type="button" class="text-red-500" :disabled="dnForm.items.length <= 1" @click="dnForm.items.splice(i, 1)">×</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="px-5 sm:px-6 py-4 border-t bg-white flex gap-3">
            <button type="button" class="flex-1 border border-slate-300 py-2.5 rounded-lg text-sm" @click="showDnForm = false">إلغاء</button>
            <button type="button" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-medium disabled:opacity-60" :disabled="dnSaving" @click="addDeliveryNote">
              {{ dnSaving ? 'جاري الحفظ...' : 'حفظ Delivery Note' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- View Delivery Note modal (designed document preview) -->
    <Teleport to="body">
      <div v-if="viewDn" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-6" dir="rtl" @click.self="closeDnView">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[95vh] overflow-hidden flex flex-col">
          <div class="px-5 sm:px-6 py-4 border-b flex items-center justify-between gap-3 bg-gradient-to-l from-blue-50 to-white">
            <div>
              <p class="text-[11px] uppercase tracking-wide text-blue-700 font-semibold">دليفري نوت</p>
              <h3 class="text-lg font-bold text-slate-900">{{ viewDn.number }}</h3>
            </div>
            <div class="flex items-center gap-2">
              <button type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg text-sm font-medium" @click="downloadDnPdf(viewDn)">
                تصدير PDF
              </button>
              <button type="button" class="text-slate-400 hover:text-slate-700 text-2xl leading-none px-1" @click="closeDnView">×</button>
            </div>
          </div>
          <div class="flex-1 overflow-auto bg-slate-100">
            <div v-if="viewDnLoading" class="p-16 text-center">
              <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
              <p class="text-sm text-slate-500 mt-3">جاري تحميل النموذج...</p>
            </div>
            <iframe
              v-else-if="viewDnHtml"
              :srcdoc="viewDnHtml"
              class="w-full min-h-[90vh] border-0 bg-slate-100"
              title="دليفري نوت"
            />
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api, { fetchAdminHtml } from '@/lib/api'
import { downloadReceiptPdf, downloadDeliveryNotePdf, deliveryNoteHtmlPath } from '@/lib/receiptPdf'

interface Customer { id: number; name: string; phone?: string; email?: string }
interface ProjectFileRow { id: number; label: string; path: string; visibility: string; kind: string }
interface PendingFile { label: string; file: File | null; preview?: string | null }
interface DocRow {
  id: number
  number: string
  date: string
  total?: number
  amount?: number
  currency?: string
  status?: string
  project_id?: number | null
  quotation?: {
    items?: {
      id: number
      description?: string
      quantity?: number | string
      code?: string
      is_section?: boolean
    }[]
  }
}

const route = useRoute()
const router = useRouter()
const isNew = computed(() => !route.params.id || route.params.id === 'new')

const pageLoading = ref(!isNew.value)
const submitting = ref(false)
const isLocked = ref(false)
const qrPath = ref('')

const form = ref({
  title: '',
  title_ar: '',
  description: '',
  description_ar: '',
  customer_id: null as number | null,
  location: '',
  maps_url: '',
  status: 'in_progress',
  is_public: false,
  order: 0,
})

const contacts = ref<{ name: string; phone: string }[]>([])
const allFiles = ref<ProjectFileRow[]>([])
const keepFileIds = ref<number[]>([])
const pendingPublic = ref<PendingFile[]>([])
const pendingPrivate = ref<PendingFile[]>([])
const linkedQuotations = ref<DocRow[]>([])
const linkedInvoices = ref<DocRow[]>([])
const showLinkInvoiceModal = ref(false)
const showLinkQuotationModal = ref(false)
const linkOptionsInvoices = ref<DocRow[]>([])
const linkOptionsQuotations = ref<DocRow[]>([])
const selectedLinkInvoiceId = ref<number | null>(null)
const selectedLinkQuotationId = ref<number | null>(null)
const linkSaving = ref(false)

const primaryInvoice = computed(() => linkedInvoices.value[0] || null)
const primaryQuotation = computed(() => linkedQuotations.value[0] || null)

const todayStr = () => new Date().toISOString().slice(0, 10)
const finance = ref({
  contract_value: 0,
  expenses_total: 0,
  profit: 0,
  payments_total: 0,
  balance_due: 0,
  capital_total: 0,
  shares_total: 0,
  company_profit: 0,
})
const payments = ref<any[]>([])
const expenses = ref<any[]>([])
const profitShares = ref<any[]>([])
const deliveryNotes = ref<any[]>([])
const expensePresets = ref<string[]>([])

const paymentForm = ref({
  amount: null as number | null,
  payment_type: 'cash',
  paid_at: todayStr(),
  notes: '',
  receipt: null as File | null,
})
const paymentSaving = ref(false)
const showPaymentForm = ref(false)
const expenseForm = ref({ name: '', amount: null as number | null, receipt: null as File | null })
const expenseSaving = ref(false)
const showExpenseForm = ref(false)
const shareForm = ref({ name: '', share_type: 'capital' as 'percent' | 'capital', value: null as number | null })
const shareSaving = ref(false)
const showShareForm = ref(false)
const showDnForm = ref(false)
const dnSaving = ref(false)
const viewDn = ref<any>(null)
const viewDnHtml = ref('')
const viewDnLoading = ref(false)
const dnItemOpen = ref<number | null>(null)
const dnForm = ref({
  title: '',
  delivered_at: todayStr(),
  notes: '',
  received_by: '',
  delivered_by: 'SmartFlow',
  items: [{ description: '', search: '', quantity: 1, unit: 'pcs', code: '' }] as { description: string; search: string; quantity: number; unit: string; code: string }[],
})

const invoiceLineItems = computed(() => {
  const out: { key: string; invoice_number: string; description: string; quantity: number; code?: string; unit: string }[] = []
  const seen = new Set<string>()
  for (const inv of linkedInvoices.value) {
    const rows = (inv as any).quotation?.items || []
    for (const it of rows) {
      if (it.is_section) continue
      const desc = String(it.description || '').trim()
      if (!desc) continue
      const dedupe = desc.toLowerCase()
      if (seen.has(dedupe)) continue
      seen.add(dedupe)
      out.push({
        key: `${inv.id}-${it.id}`,
        invoice_number: inv.number || String(inv.id),
        description: desc,
        quantity: Number(it.quantity || 1),
        code: it.code || undefined,
        unit: 'pcs',
      })
    }
  }
  return out
})

const filteredDnProducts = (q: string) => {
  const s = (q || '').trim().toLowerCase()
  const list = invoiceLineItems.value
  if (!s) return list.slice(0, 40)
  return list
    .filter((p) =>
      p.description.toLowerCase().includes(s) ||
      (p.code || '').toLowerCase().includes(s) ||
      p.invoice_number.toLowerCase().includes(s)
    )
    .slice(0, 40)
}

const pickDnProduct = (index: number, p: { description: string; quantity: number; unit: string; code?: string }) => {
  const row = dnForm.value.items[index]
  if (!row) return
  row.description = p.description
  row.search = p.description
  row.quantity = p.quantity
  row.unit = p.unit || 'pcs'
  row.code = p.code || ''
  dnItemOpen.value = null
}

const addDnItemRow = () => {
  dnForm.value.items.push({ description: '', search: '', quantity: 1, unit: 'pcs', code: '' })
}

const resetDnForm = () => {
  dnForm.value = {
    title: '',
    delivered_at: todayStr(),
    notes: '',
    received_by: selectedCustomer.value?.name || '',
    delivered_by: 'SmartFlow',
    items: [{ description: '', search: '', quantity: 1, unit: 'pcs', code: '' }],
  }
  dnItemOpen.value = null
}

const openPaymentForm = () => {
  paymentForm.value = { amount: null, payment_type: 'cash', paid_at: todayStr(), notes: '', receipt: null }
  showPaymentForm.value = true
}

const openExpenseForm = () => {
  expenseForm.value = { name: '', amount: null, receipt: null }
  showExpenseForm.value = true
}

const openShareForm = () => {
  shareForm.value = { name: '', share_type: 'capital', value: null }
  showShareForm.value = true
}

const openDnCreate = () => {
  resetDnForm()
  showDnForm.value = true
}

const closeDnView = () => {
  viewDn.value = null
  viewDnHtml.value = ''
}

const openDnView = async (dn: any) => {
  viewDn.value = dn
  viewDnHtml.value = ''
  viewDnLoading.value = true
  try {
    viewDnHtml.value = await fetchAdminHtml(deliveryNoteHtmlPath(route.params.id as string, dn.id))
  } catch {
    viewDnHtml.value = ''
    alert('تعذر تحميل نموذج الدليفري نوت')
    viewDn.value = null
  } finally {
    viewDnLoading.value = false
  }
}

const formatDnDate = (d: string) => {
  try {
    return new Date(d).toLocaleDateString('en-GB')
  } catch {
    return d
  }
}

const quoteStatusLabel = (s?: string) =>
  ({ draft: 'مسودة', sent: 'مرسل', accepted: 'مقبول', cancelled: 'ملغى' } as Record<string, string>)[s || ''] || (s || '—')

const quoteStatusClass = (s?: string) =>
  ({
    draft: 'bg-gray-100 text-gray-700',
    sent: 'bg-blue-100 text-blue-700',
    accepted: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-red-100 text-red-700',
  } as Record<string, string>)[s || ''] || 'bg-gray-100 text-gray-600'

const invoiceStatusLabel = (s?: string) =>
  ({ draft: 'مسودة', sent: 'مرسلة', paid: 'مدفوعة', cancelled: 'ملغاة' } as Record<string, string>)[s || ''] || (s || '—')

const invoiceStatusClass = (s?: string) =>
  ({
    draft: 'bg-gray-100 text-gray-700',
    sent: 'bg-amber-100 text-amber-800',
    paid: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-red-100 text-red-700',
  } as Record<string, string>)[s || ''] || 'bg-gray-100 text-gray-600'

const downloadDnPdf = async (dn: any) => {
  try {
    await downloadDeliveryNotePdf(route.params.id as string, dn.id, dn.number)
  } catch {
    alert('تعذر تصدير PDF')
  }
}

const money = (n: number) =>
  `AED ${Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

const paymentTypeLabel = (t: string) =>
  ({ cash: 'كاش', bank: 'بنكي', card: 'بطاقة', transfer: 'حوالة', cheque: 'شيك', other: 'أخرى' } as Record<string, string>)[t] || t

const applyFinance = (f: any) => {
  if (!f) return
  finance.value = {
    contract_value: Number(f.contract_value || 0),
    expenses_total: Number(f.expenses_total || 0),
    profit: Number(f.profit || 0),
    payments_total: Number(f.payments_total || 0),
    balance_due: Number(f.balance_due || 0),
    capital_total: Number(f.capital_total || 0),
    shares_total: Number(f.shares_total || 0),
    company_profit: Number(f.company_profit ?? f.profit ?? 0),
  }
  if (Array.isArray(f.shares)) {
    profitShares.value = f.shares
  }
}

const onReceiptFile = (e: Event) => {
  paymentForm.value.receipt = (e.target as HTMLInputElement).files?.[0] || null
}

const onExpenseReceiptFile = (e: Event) => {
  expenseForm.value.receipt = (e.target as HTMLInputElement).files?.[0] || null
}

const customers = ref<Customer[]>([])
const customerQuery = ref('')
const customerOpen = ref(false)
const customerWrap = ref<HTMLElement | null>(null)

const selectedCustomer = computed(() => customers.value.find((c) => c.id === form.value.customer_id) || null)
const publicFiles = computed(() => allFiles.value.filter((f) => f.visibility === 'public'))
const privateFiles = computed(() => allFiles.value.filter((f) => f.visibility === 'private'))
const visiblePublicFiles = computed(() => publicFiles.value.filter((f) => keepFileIds.value.includes(f.id)))
const visiblePrivateFiles = computed(() => privateFiles.value.filter((f) => keepFileIds.value.includes(f.id)))
const mapsLink = computed(() => form.value.maps_url || (form.value.location ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(form.value.location)}` : ''))

const showFilePicker = ref(false)
const filePickerKind = ref<'public' | 'private'>('public')
const filePickerLabel = ref('')
const filePickerFile = ref<File | null>(null)
const filePickerPreview = ref<string | null>(null)
const filePickerInput = ref<HTMLInputElement | null>(null)

const isImagePath = (path: string) => /\.(jpe?g|png|gif|webp|bmp|svg)(\?|$)/i.test(path || '')
const fileExt = (path: string) => {
  const m = (path || '').split('?')[0].split('.').pop()
  return (m || 'FILE').slice(0, 5)
}

const revokePreview = (url?: string | null) => {
  if (url && url.startsWith('blob:')) URL.revokeObjectURL(url)
}

const openFilePicker = (kind: 'public' | 'private') => {
  filePickerKind.value = kind
  filePickerLabel.value = ''
  filePickerFile.value = null
  revokePreview(filePickerPreview.value)
  filePickerPreview.value = null
  showFilePicker.value = true
}

const closeFilePicker = () => {
  showFilePicker.value = false
  revokePreview(filePickerPreview.value)
  filePickerPreview.value = null
  filePickerFile.value = null
  filePickerLabel.value = ''
  if (filePickerInput.value) filePickerInput.value.value = ''
}

const onFilePickerChange = (e: Event) => {
  const f = (e.target as HTMLInputElement).files?.[0] || null
  revokePreview(filePickerPreview.value)
  filePickerFile.value = f
  filePickerPreview.value = f && f.type.startsWith('image/') ? URL.createObjectURL(f) : null
  if (f && !filePickerLabel.value.trim()) {
    filePickerLabel.value = f.name.replace(/\.[^.]+$/, '')
  }
}

const confirmFilePicker = () => {
  if (!filePickerFile.value) {
    alert('اختر ملفاً')
    return
  }
  if (!filePickerLabel.value.trim()) {
    alert('أدخل اسم الملف')
    return
  }
  const row: PendingFile = {
    label: filePickerLabel.value.trim(),
    file: filePickerFile.value,
    preview: filePickerPreview.value,
  }
  // keep preview owned by pending row
  filePickerPreview.value = null
  if (filePickerKind.value === 'public') pendingPublic.value.push(row)
  else pendingPrivate.value.push(row)
  showFilePicker.value = false
  filePickerFile.value = null
  filePickerLabel.value = ''
  if (filePickerInput.value) filePickerInput.value.value = ''
}

const removePending = (kind: 'public' | 'private', index: number) => {
  const list = kind === 'public' ? pendingPublic : pendingPrivate
  const row = list.value[index]
  revokePreview(row?.preview)
  list.value.splice(index, 1)
}

const filteredCustomers = computed(() => {
  const q = customerQuery.value.trim().toLowerCase()
  const list = customers.value
  if (!q) return list.slice(0, 30)
  return list.filter((c) => c.name.toLowerCase().includes(q) || (c.phone || '').includes(q)).slice(0, 30)
})

const selectCustomer = (c: Customer) => {
  form.value.customer_id = c.id
  customerQuery.value = ''
  customerOpen.value = false
}
const clearCustomer = () => {
  form.value.customer_id = null
  customerQuery.value = ''
}

const syncMapsUrl = () => {
  if (!form.value.maps_url && form.value.location) {
    form.value.maps_url = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(form.value.location)}`
  }
}
const openMaps = () => { if (mapsLink.value) window.open(mapsLink.value, '_blank') }
const copyMaps = async () => {
  if (!mapsLink.value) return
  try { await navigator.clipboard.writeText(mapsLink.value); alert('تم نسخ الرابط') } catch { alert(mapsLink.value) }
}

const addContact = () => contacts.value.push({ name: '', phone: '' })
const toggleKeepFile = (id: number) => { keepFileIds.value = keepFileIds.value.filter((x) => x !== id) }

const loadCustomers = async () => {
  try {
    const res = await api.get('/admin/customers')
    customers.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error(e)
  }
}

const ensureCustomerInList = (c: Customer | null | undefined) => {
  if (!c?.id) return
  if (!customers.value.some((x) => x.id === c.id)) {
    customers.value = [c, ...customers.value]
  }
}

const invoiceOptionLabel = (inv: DocRow) => {
  const base = `${inv.number} · ${money(Number(inv.total || inv.amount || 0))}`
  if (inv.project_id && inv.project_id !== Number(route.params.id)) return `${base} (مربوطة بمشروع آخر)`
  return base
}

const quotationOptionLabel = (q: DocRow) => {
  const base = `${q.number} · ${money(Number(q.total || 0))}`
  if (q.project_id && q.project_id !== Number(route.params.id)) return `${base} (مربوط بمشروع آخر)`
  return base
}

const applyProjectDocs = (p: any) => {
  linkedQuotations.value = p.quotations || []
  linkedInvoices.value = p.invoices || []
  if (p.finance) applyFinance(p.finance)
}

const loadLinkOptions = async () => {
  const res = await api.get(`/admin/projects/${route.params.id}/link-options`)
  linkOptionsInvoices.value = res.data.invoices || []
  linkOptionsQuotations.value = res.data.quotations || []
  selectedLinkInvoiceId.value = res.data.linked_invoice_id || primaryInvoice.value?.id || null
  selectedLinkQuotationId.value = res.data.linked_quotation_id || primaryQuotation.value?.id || null
}

const openLinkInvoiceModal = async () => {
  if (!form.value.customer_id) {
    alert('اختر العميل أولاً')
    return
  }
  try {
    await loadLinkOptions()
    showLinkInvoiceModal.value = true
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر تحميل الفواتير')
  }
}

const openLinkQuotationModal = async () => {
  if (!form.value.customer_id) {
    alert('اختر العميل أولاً')
    return
  }
  try {
    await loadLinkOptions()
    showLinkQuotationModal.value = true
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر تحميل العروض')
  }
}

const saveLinkInvoice = async () => {
  if (!selectedLinkInvoiceId.value) return
  linkSaving.value = true
  try {
    const res = await api.put(`/admin/projects/${route.params.id}/link-invoice`, {
      invoice_id: selectedLinkInvoiceId.value,
    })
    applyProjectDocs(res.data)
    showLinkInvoiceModal.value = false
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر ربط الفاتورة')
  } finally {
    linkSaving.value = false
  }
}

const saveLinkQuotation = async () => {
  if (!selectedLinkQuotationId.value) return
  linkSaving.value = true
  try {
    const res = await api.put(`/admin/projects/${route.params.id}/link-quotation`, {
      quotation_id: selectedLinkQuotationId.value,
    })
    applyProjectDocs(res.data)
    showLinkQuotationModal.value = false
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر ربط عرض السعر')
  } finally {
    linkSaving.value = false
  }
}

const loadProject = async () => {
  if (isNew.value) return
  pageLoading.value = true
  try {
    const res = await api.get(`/admin/projects/${route.params.id}`)
    const p = res.data
    ensureCustomerInList(p.customer)
    form.value = {
      title: p.title,
      title_ar: p.title_ar,
      description: p.description,
      description_ar: p.description_ar,
      customer_id: p.customer_id,
      location: p.location || '',
      maps_url: p.maps_url || '',
      status: p.status || 'in_progress',
      is_public: !!p.is_public,
      order: p.order || 0,
    }
    isLocked.value = p.status === 'completed'
    qrPath.value = p.qr_path || ''
    contacts.value = (p.contacts || []).map((c: { name: string; phone?: string }) => ({ name: c.name, phone: c.phone || '' }))
    allFiles.value = p.files || []
    keepFileIds.value = allFiles.value.map((f) => f.id)
    linkedQuotations.value = p.quotations || []
    linkedInvoices.value = p.invoices || []
    payments.value = p.payments || []
    expenses.value = p.expenses || []
    deliveryNotes.value = p.delivery_notes || []
    expensePresets.value = p.expense_presets || []
    applyFinance(p.finance)
    if (!Array.isArray(p.finance?.shares) && Array.isArray(p.profit_shares)) {
      profitShares.value = p.profit_shares
    }
  } catch (e) {
    console.error(e)
    alert('تعذر تحميل المشروع')
  } finally {
    pageLoading.value = false
  }
}

const addPayment = async () => {
  if (!paymentForm.value.amount) {
    alert('أدخل مبلغ الدفعة')
    return
  }
  paymentSaving.value = true
  try {
    const fd = new FormData()
    fd.append('amount', String(paymentForm.value.amount))
    fd.append('payment_type', paymentForm.value.payment_type)
    fd.append('paid_at', paymentForm.value.paid_at || todayStr())
    if (paymentForm.value.notes) fd.append('notes', paymentForm.value.notes)
    if (paymentForm.value.receipt) fd.append('receipt', paymentForm.value.receipt)
    const res = await api.post(`/admin/projects/${route.params.id}/payments`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    payments.value = [res.data.payment, ...payments.value]
    applyFinance(res.data.finance)
    paymentForm.value = { amount: null, payment_type: 'cash', paid_at: todayStr(), notes: '', receipt: null }
    showPaymentForm.value = false
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر حفظ الدفعة')
  } finally {
    paymentSaving.value = false
  }
}

const removePayment = async (id: number) => {
  if (!confirm('حذف الدفعة؟')) return
  try {
    const res = await api.delete(`/admin/projects/${route.params.id}/payments/${id}`)
    payments.value = payments.value.filter((p) => p.id !== id)
    applyFinance(res.data.finance)
  } catch {
    alert('تعذر الحذف')
  }
}

const downloadPaymentPdf = async (p: { id: number }) => {
  try {
    await downloadReceiptPdf(route.params.id as string, p.id)
  } catch (e: any) {
    alert(e.message || e.response?.data?.message || 'تعذر تصدير الوصل')
  }
}

const viewPaymentPdf = (p: { id: number }) => {
  router.push(`/admin/payments/receipt/${route.params.id}/${p.id}`)
}

const addExpense = async () => {
  if (!expenseForm.value.name.trim() || !expenseForm.value.amount) {
    alert('أدخل اسم المصروف والقيمة')
    return
  }
  expenseSaving.value = true
  try {
    const fd = new FormData()
    fd.append('name', expenseForm.value.name.trim())
    fd.append('amount', String(expenseForm.value.amount))
    fd.append('spent_at', todayStr())
    if (expenseForm.value.receipt) fd.append('receipt', expenseForm.value.receipt)
    const res = await api.post(`/admin/projects/${route.params.id}/expenses`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    expenses.value = [res.data.expense, ...expenses.value]
    applyFinance(res.data.finance)
    expenseForm.value = { name: '', amount: null, receipt: null }
    showExpenseForm.value = false
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر حفظ المصروف')
  } finally {
    expenseSaving.value = false
  }
}

const removeExpense = async (id: number) => {
  if (!confirm('حذف المصروف؟')) return
  try {
    const res = await api.delete(`/admin/projects/${route.params.id}/expenses/${id}`)
    expenses.value = expenses.value.filter((e) => e.id !== id)
    applyFinance(res.data.finance)
  } catch {
    alert('تعذر الحذف')
  }
}

const addShare = async () => {
  if (!shareForm.value.name.trim() || !shareForm.value.value) {
    alert('أدخل الاسم والقيمة')
    return
  }
  if (shareForm.value.share_type === 'percent' && shareForm.value.value > 100) {
    alert('النسبة لا تتجاوز 100%')
    return
  }
  shareSaving.value = true
  try {
    const res = await api.post(`/admin/projects/${route.params.id}/profit-shares`, {
      name: shareForm.value.name.trim(),
      share_type: shareForm.value.share_type,
      value: shareForm.value.value,
    })
    applyFinance(res.data.finance)
    shareForm.value = { name: '', share_type: 'capital', value: null }
    showShareForm.value = false
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر حفظ الحصة')
  } finally {
    shareSaving.value = false
  }
}

const removeShare = async (id: number) => {
  if (!confirm('حذف هذا الشخص من توزيع الربح؟')) return
  try {
    const res = await api.delete(`/admin/projects/${route.params.id}/profit-shares/${id}`)
    applyFinance(res.data.finance)
  } catch {
    alert('تعذر الحذف')
  }
}

const addDeliveryNote = async () => {
  if (!dnForm.value.title.trim()) {
    alert('أدخل عنوان التسليم')
    return
  }
  const items = dnForm.value.items.filter((i) => i.description.trim())
  if (!items.length) {
    alert(invoiceLineItems.value.length ? 'اختر منتجاً من الفاتورة لكل بند' : 'لا بنود فاتورة — أنشئ فاتورة أولاً')
    return
  }
  dnSaving.value = true
  try {
    const res = await api.post(`/admin/projects/${route.params.id}/delivery-notes`, {
      title: dnForm.value.title,
      delivered_at: dnForm.value.delivered_at || todayStr(),
      notes: dnForm.value.notes || null,
      received_by: dnForm.value.received_by || null,
      delivered_by: dnForm.value.delivered_by || null,
      items: items.map((i) => ({
        description: i.description,
        quantity: i.quantity,
        unit: i.unit,
        code: i.code || null,
      })),
    })
    deliveryNotes.value = [res.data, ...deliveryNotes.value]
    showDnForm.value = false
    await openDnView(res.data)
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر حفظ Delivery Note')
  } finally {
    dnSaving.value = false
  }
}

const removeDeliveryNote = async (id: number) => {
  if (!confirm('حذف Delivery Note؟')) return
  try {
    await api.delete(`/admin/projects/${route.params.id}/delivery-notes/${id}`)
    deliveryNotes.value = deliveryNotes.value.filter((d) => d.id !== id)
  } catch {
    alert('تعذر الحذف')
  }
}

const handleSubmit = async () => {
  if (isLocked.value) return
  submitting.value = true
  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([k, v]) => {
      if (k === 'customer_id') {
        fd.append('customer_id', v ? String(v) : '')
        return
      }
      if (v !== null && v !== undefined) fd.append(k, String(v))
    })
    fd.append('is_public', form.value.is_public ? '1' : '0')
    fd.append('contacts', JSON.stringify(contacts.value.filter((c) => c.name.trim())))
    fd.append('keep_file_ids', JSON.stringify(keepFileIds.value))

    pendingPublic.value.forEach((row) => {
      if (row.file) {
        fd.append('public_files[]', row.file)
        fd.append('public_labels[]', row.label || row.file.name)
      }
    })
    pendingPrivate.value.forEach((row) => {
      if (row.file) {
        fd.append('private_files[]', row.file)
        fd.append('private_labels[]', row.label || row.file.name)
      }
    })

    const multipart = { headers: { 'Content-Type': 'multipart/form-data' } }
    if (isNew.value) {
      const res = await api.post('/projects', fd, multipart)
      alert('تم إنشاء المشروع')
      pendingPublic.value.forEach((r) => revokePreview(r.preview))
      pendingPrivate.value.forEach((r) => revokePreview(r.preview))
      pendingPublic.value = []
      pendingPrivate.value = []
      if (res.data?.id) {
        await router.replace(`/admin/projects/${res.data.id}`)
        await nextTick()
        await loadProject()
        return
      }
    } else {
      await api.post(`/projects/${route.params.id}`, fd, multipart)
      alert('تم حفظ المشروع')
      pendingPublic.value.forEach((r) => revokePreview(r.preview))
      pendingPrivate.value.forEach((r) => revokePreview(r.preview))
      pendingPublic.value = []
      pendingPrivate.value = []
      await loadProject()
      return
    }
    router.push('/admin/projects')
  } catch (e: any) {
    console.error(e)
    const msg =
      e.response?.data?.message ||
      (e.response?.data?.errors && Object.values(e.response.data.errors).flat().join('\n')) ||
      'تعذر الحفظ'
    alert(msg)
  } finally {
    submitting.value = false
  }
}

const onDocClick = (e: MouseEvent) => {
  if (!customerWrap.value?.contains(e.target as Node)) customerOpen.value = false
}

onMounted(async () => {
  document.addEventListener('click', onDocClick)
  await loadCustomers()
  await loadProject()
})
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))
</script>
