import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '@/views/HomePage.vue'
import AllProductsPage from '@/views/AllProductsPage.vue'
import ProductDetails from '@/views/ProductDetails.vue'
import AdminLogin from '@/views/AdminLogin.vue'
import AdminLayout from '@/views/AdminLayout.vue'
import AdminDashboard from '@/views/AdminDashboard.vue'
import AdminProducts from '@/views/AdminProducts.vue'
import AdminProductEdit from '@/views/AdminProductEdit.vue'
import AdminOffers from '@/views/AdminOffers.vue'
import AdminCategories from '@/views/AdminCategories.vue'
import AdminCategoryEdit from '@/views/AdminCategoryEdit.vue'
import AdminGroups from '@/views/AdminGroups.vue'
import AdminGroupEdit from '@/views/AdminGroupEdit.vue'
import AdminProjectsPage from '@/views/AdminProjectsPage.vue'
import AdminProjectEdit from '@/views/AdminProjectEdit.vue'
import AdminProjectMastersPage from '@/views/AdminProjectMastersPage.vue'
import AdminProjectMasterEdit from '@/views/AdminProjectMasterEdit.vue'
import AdminPayments from '@/views/AdminPayments.vue'
import AdminPaymentReceiptView from '@/views/AdminPaymentReceiptView.vue'
import AdminSettings from '@/views/AdminSettings.vue'
import AdminReviews from '@/views/AdminReviews.vue'
import GroupPage from '@/views/GroupPage.vue'
import AdminAppointments from '@/views/AdminAppointments.vue'
import AdminStudyRequests from '@/views/AdminStudyRequests.vue'
import AdminCustomers from '@/views/AdminCustomers.vue'
import AdminEngineers from '@/views/AdminEngineers.vue'
import AdminReports from '@/views/AdminReports.vue'
import AdminReportEdit from '@/views/AdminReportEdit.vue'
import AdminReportView from '@/views/AdminReportView.vue'
import AdminQuotations from '@/views/AdminQuotations.vue'
import AdminQuotationEdit from '@/views/AdminQuotationEdit.vue'
import AdminInvoices from '@/views/AdminInvoices.vue'
import AdminInvoiceView from '@/views/AdminInvoiceView.vue'
import AdminDeliveryNotes from '@/views/AdminDeliveryNotes.vue'
import ProjectsPage from '@/views/ProjectsPage.vue'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior(to) {
    if (to.hash) {
      return { el: to.hash, behavior: 'smooth', top: 80 }
    }
    return { top: 0 }
  },
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomePage,
    },
    {
      path: '/products',
      name: 'all-products',
      component: AllProductsPage,
    },
    {
      path: '/projects',
      name: 'our-projects',
      component: ProjectsPage,
    },
    {
      path: '/groups/:id',
      name: 'group',
      component: GroupPage,
    },
    {
      path: '/offers',
      name: 'all-offers',
      component: () => import('@/views/AllOffersPage.vue'),
    },
    {
      path: '/project-study',
      name: 'project-study',
      component: () => import('@/views/ProjectStudyPage.vue'),
    },
    {
      path: '/gate-machine-study',
      name: 'gate-machine-study',
      component: () => import('@/views/GateMachineStudyPage.vue'),
    },
    {
      path: '/products/:id',
      name: 'product-details',
      component: ProductDetails,
    },
    {
      path: '/admin',
      name: 'admin-login',
      component: AdminLogin,
    },
    {
      path: '/admin',
      component: AdminLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: 'dashboard',
          name: 'admin-dashboard',
          component: AdminDashboard,
        },
        {
          path: 'products',
          name: 'admin-products',
          component: AdminProducts,
        },
        {
          path: 'products/new',
          name: 'admin-product-new',
          component: AdminProductEdit,
        },
        {
          path: 'products/:id',
          name: 'admin-product-edit',
          component: AdminProductEdit,
        },
        {
          path: 'offers',
          name: 'admin-offers',
          component: AdminOffers,
        },
        {
          path: 'categories',
          name: 'admin-categories',
          component: AdminCategories,
        },
        {
          path: 'categories/new',
          name: 'admin-category-new',
          component: AdminCategoryEdit,
        },
        {
          path: 'categories/:id',
          name: 'admin-category-edit',
          component: AdminCategoryEdit,
        },
        {
          path: 'groups',
          name: 'admin-groups',
          component: AdminGroups,
        },
        {
          path: 'groups/new',
          name: 'admin-group-new',
          component: AdminGroupEdit,
        },
        {
          path: 'groups/:id',
          name: 'admin-group-edit',
          component: AdminGroupEdit,
        },
        {
          path: 'projects',
          name: 'admin-projects',
          component: AdminProjectsPage,
        },
        {
          path: 'projects/new',
          name: 'admin-project-new',
          component: AdminProjectEdit,
        },
        {
          path: 'projects/:id',
          name: 'admin-project-edit',
          component: AdminProjectEdit,
        },
        {
          path: 'project-masters',
          name: 'admin-project-masters',
          component: AdminProjectMastersPage,
        },
        {
          path: 'project-masters/new',
          name: 'admin-project-master-new',
          component: AdminProjectMasterEdit,
        },
        {
          path: 'project-masters/:id',
          name: 'admin-project-master-edit',
          component: AdminProjectMasterEdit,
        },
        {
          path: 'payments',
          name: 'admin-payments',
          component: AdminPayments,
        },
        {
          path: 'payments/receipt/:projectId/:paymentId',
          name: 'admin-payment-receipt',
          component: AdminPaymentReceiptView,
        },
        {
          path: 'settings',
          name: 'admin-settings',
          component: AdminSettings,
        },
        {
          path: 'reviews',
          name: 'admin-reviews',
          component: AdminReviews,
        },
        {
          path: 'appointments',
          name: 'admin-appointments',
          component: AdminAppointments,
        },
        {
          path: 'study-requests',
          name: 'admin-study-requests',
          component: AdminStudyRequests,
        },
        {
          path: 'gate-machine-studies',
          redirect: { name: 'admin-study-requests' },
        },
        {
          path: 'customers',
          name: 'admin-customers',
          component: AdminCustomers,
        },
        {
          path: 'engineers',
          name: 'admin-engineers',
          component: AdminEngineers,
        },
        {
          path: 'reports',
          name: 'admin-reports',
          component: AdminReports,
        },
        {
          path: 'reports/new',
          name: 'admin-report-new',
          component: AdminReportEdit,
        },
        {
          path: 'reports/:id/view',
          name: 'admin-report-view',
          component: AdminReportView,
        },
        {
          path: 'reports/:id',
          name: 'admin-report-edit',
          component: AdminReportEdit,
        },
        {
          path: 'quotations',
          name: 'admin-quotations',
          component: AdminQuotations,
        },
        {
          path: 'quotations/new',
          name: 'admin-quotation-new',
          component: AdminQuotationEdit,
        },
        {
          path: 'quotations/:id',
          name: 'admin-quotation-edit',
          component: AdminQuotationEdit,
        },
        {
          path: 'invoices',
          name: 'admin-invoices',
          component: AdminInvoices,
        },
        {
          path: 'invoices/:id',
          name: 'admin-invoice-view',
          component: AdminInvoiceView,
        },
        {
          path: 'delivery-notes',
          name: 'admin-delivery-notes',
          component: AdminDeliveryNotes,
        },
      ],
    },
  ],
})

// Navigation guard
router.beforeEach((to, _from, next) => {
  const isLoggedIn = sessionStorage.getItem('adminLoggedIn')
  
  if (to.meta.requiresAuth && !isLoggedIn) {
    next('/admin')
  } else if (to.path === '/admin' && isLoggedIn) {
    next('/admin/dashboard')
  } else {
    next()
  }
})

export default router
