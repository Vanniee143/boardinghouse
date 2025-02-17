import { createRouter, createWebHistory } from 'vue-router';

// Admin routes
import AdminPanel from './components/admin/AdminPanel.vue';
import AdminRooms from './components/admin/AdminRooms.vue';
import AdminAccounts from './components/admin/AdminAccounts.vue';
import AdminReviews from './components/admin/AdminReviews.vue';
import AddRoom from './components/admin/AddRoom.vue';
import AddBoardingHouse from './components/admin/AddBoardingHouse.vue';
import AdminLogin from './components/admin/AdminLogin.vue';
import EditAccount from './components/admin/EditAccount.vue';
import AddUser from './components/admin/AddUser.vue';
import AdminBoardingHouse from './components/admin/AdminBoardingHouse.vue';
import EditBoardingHouse from './components/admin/EditBoardingHouse.vue';
import EditRoom from './components/admin/EditRoom.vue';
import AdminProfile from './components/admin/AdminProfile.vue';
import AdminBookings from './components/admin/AdminBookings.vue';
import AdminRevenue from './components/admin/AdminRevenue.vue';
import PaymentSettings from './components/admin/PaymentSettings.vue';
import AdminTenants from './components/admin/AdminTenants.vue';
import PaymentManagement from './components/admin/PaymentManagement.vue';

// User routes
import UserLogin from './components/user/userLogin.vue';
import UserRegister from './components/user/userRegister.vue';
import UserHomePage from './components/user/UserHomePage.vue';
import UserBooking from './components/user/UserBooking.vue';
import UserReviews from './components/user/UserReviews.vue';
import UserAccount from './components/user/UserAccount.vue';
import UserHelp from './components/user/UserHelp.vue';
import UserRecentlySearched from './components/user/UserRecentlySearched.vue';
import BHList from './components/user/BHList.vue';
import UserViewBooking from './components/user/UserViewBooking.vue';
import Rooms from './components/user/Rooms.vue';

// Landlord routes
import LandlordLogin from './components/landlord/LandlordLogin.vue';
import LandlordPanel from './components/landlord/LandlordPanel.vue';
import LandlordBoardingHouses from './components/landlord/LandlordBoardingHouses.vue';
import LandlordBookings from './components/landlord/LandlordBookings.vue';
import LandlordReviews from './components/landlord/LandlordReviews.vue';
import LandlordRevenue from './components/landlord/LandlordRevenue.vue';
import LandlordAddBoardingHouse from './components/landlord/LandlordAddBoardingHouse.vue';
import LandlordAddRoom from './components/landlord/LandlordAddRoom.vue';
import LandlordRooms from './components/landlord/LandlordRooms.vue';
import LandlordEditRoom from './components/landlord/LandlordEditRoom.vue';
import LandlordEditBoardingHouse from './components/landlord/LandlordEditBoardingHouse.vue';
import LandlordProfile from './components/landlord/LandlordProfile.vue';
import LandlordPaymentSettings from './components/landlord/LandlordPaymentSettings.vue';
import LandlordTenants from './components/landlord/LandlordTenants.vue';
import LandlordPaymentManagement from './components/landlord/LandlordPaymentManagement.vue';

// Public routes
import HomePage from './components/HomePage.vue';
import HelpSupport from './components/HelpSupport.vue';
import RecentlySearched from './components/RecentlySearched.vue';
import AboutUs from '@/components/AboutUs.vue'
import TermsConditions from '@/components/TermsConditions.vue'
import LegalInformation from '@/components/LegalInformation.vue'
import PrivacyNotice from '@/components/PrivacyNotice.vue'
import PersonalInformation from '@/components/PersonalInformation.vue'
import Help from '@/components/Help.vue'
import CyberSecurity from '@/components/CyberSecurity.vue'
import HowItWorks from '@/components/HowItWorks.vue'

// Enhanced authentication check
function isAuthenticated(requiredType = null) {
    const token = localStorage.getItem('token');
    const userType = localStorage.getItem('userType');
    
    if (!token || !userType) {
        return false;
    }
    
    if (requiredType && userType !== requiredType) {
        return false;
    }
    
    return true;
}

// Route guard function
function guardRoute(to, from, next, requiredType) {
    const userType = localStorage.getItem('userType');
    
    if (!isAuthenticated()) {
        // Not authenticated at all - redirect to appropriate login
        switch (requiredType) {
            case 'admin':
                next({ name: 'admin.login' });
                break;
            case 'landlord':
                next({ name: 'landlord.login' });
                break;
            default:
                next({ name: 'login' });
        }
        return;
    }

    if (requiredType && userType !== requiredType) {
        // Authenticated but wrong type - redirect to appropriate dashboard
        switch (userType) {
            case 'admin':
                next({ name: 'admin.dashboard' });
                break;
            case 'landlord':
                next({ name: 'landlord.dashboard' });
                break;
            case 'user':
                next({ name: 'user.home' });
                break;
            default:
                next({ name: 'home' });
        }
        return;
    }

    next();
}

const routes = [
    // Public routes (accessible only when not logged in)
    {
        path: '/',
        name: 'home',
        component: HomePage,
        beforeEnter: (to, from, next) => {
            if (isAuthenticated()) {
                const userType = localStorage.getItem('userType');
                switch (userType) {
                    case 'admin':
                        next({ name: 'admin.dashboard' });
                        break;
                    case 'landlord':
                        next({ name: 'landlord.dashboard' });
                        break;
                    case 'user':
                        next({ name: 'user.home' });
                        break;
                    default:
                        next();
                }
            } else {
                next();
            }
        }
    },
    {
        path: '/help_support',
        name: 'help.support',
        component: HelpSupport,
        beforeEnter: (to, from, next) => {
            if (isAuthenticated()) {
                const userType = localStorage.getItem('userType');
                switch (userType) {
                    case 'admin':
                        next({ name: 'admin.dashboard' });
                        break;
                    case 'landlord':
                        next({ name: 'landlord.dashboard' });
                        break;
                    case 'user':
                        next({ name: 'user.home' });
                        break;
                    default:
                        next();
                }
            } else {
                next();
            }
        }
    },
    {
        path: '/recently_searched',
        name: 'recently.searched',
        component: RecentlySearched,
        beforeEnter: (to, from, next) => {
            if (isAuthenticated()) {
                const userType = localStorage.getItem('userType');
                switch (userType) {
                    case 'admin':
                        next({ name: 'admin.dashboard' });
                        break;
                    case 'landlord':
                        next({ name: 'landlord.dashboard' });
                        break;
                    case 'user':
                        next({ name: 'user.home' });
                        break;
                    default:
                        next();
                }
            } else {
                next();
            }
        }
    },
    {
        path: '/login',
        name: 'login',
        component: UserLogin,
        beforeEnter: (to, from, next) => {
            if (isAuthenticated()) {
                const userType = localStorage.getItem('userType');
                switch (userType) {
                    case 'admin':
                        next({ name: 'admin.dashboard' });
                        break;
                    case 'landlord':
                        next({ name: 'landlord.dashboard' });
                        break;
                    case 'user':
                        next({ name: 'user.home' });
                        break;
                    default:
                        next();
                }
            } else {
                next();
            }
        }
    },
    {
        path: '/admin/admin_login',
        name: 'admin.login',
        component: AdminLogin,
        beforeEnter: (to, from, next) => {
            if (isAuthenticated()) {
                const userType = localStorage.getItem('userType');
                if (userType === 'admin') {
                    next({ name: 'admin.dashboard' });
                } else {
                    next({ name: userType + '.dashboard' });
                }
            } else {
                next();
            }
        }
    },
    {
        path: '/landlord/login',
        name: 'landlord.login',
        component: LandlordLogin,
        beforeEnter: (to, from, next) => {
            if (isAuthenticated()) {
                const userType = localStorage.getItem('userType');
                switch (userType) {
                    case 'admin':
                        next({ name: 'admin.dashboard' });
                        break;
                    case 'landlord':
                        next({ name: 'landlord.dashboard' });
                        break;
                    case 'user':
                        next({ name: 'user.home' });
                        break;
                    default:
                        next();
                }
            } else {
                next();
            }
        }
    },
    {
        path: '/register',
        name: 'register',
        component: UserRegister,
        beforeEnter: (to, from, next) => {
            if (isAuthenticated()) {
                const userType = localStorage.getItem('userType');
                switch (userType) {
                    case 'admin':
                        next({ name: 'admin.dashboard' });
                        break;
                    case 'landlord':
                        next({ name: 'landlord.dashboard' });
                        break;
                    case 'user':
                        next({ name: 'user.home' });
                        break;
                    default:
                        next();
                }
            } else {
                next();
            }
        }
    },

    // User routes
    {
        path: '/user/home',
        name: 'user.home',
        component: UserHomePage,
        beforeEnter: (to, from, next) => {
            if (!isAuthenticated('user')) {
                next({ name: 'login' });
                return;
            }
            next();
        }
    },
    {
        path: '/user/booking/:id/:boardingHouseId',
        name: 'user.booking',
        component: UserBooking,
        props: true,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'user')
    },
    {
        path: '/user/reviews/:id',
        name: 'user.reviews',
        component: () => import('./components/user/UserReviews.vue'),
        props: true
    },
    {
        path: '/user/account',
        name: 'user.account',
        component: UserAccount,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'user')
    },
    {
        path: '/user/help',
        name: 'user.help',
        component: UserHelp,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'user')
    },
    {
        path: '/user/recently_searched',
        name: 'user.recently_searched',
        component: UserRecentlySearched,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'user')
    },
    {
        path: '/user/boarding-houses',
        name: 'user.boarding-houses',
        component: BHList,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'user')
    },
    {
        path: '/user/view-booking/:id',
        name: 'user.view-booking',
        component: UserViewBooking,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'user')
    },
    {
        path: '/user/rooms/:id',
        name: 'user.rooms',
        component: Rooms,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'user')
    },

    // Admin routes
    {
        path: '/admin',
        name: 'admin.dashboard',
        component: AdminPanel,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'admin')
    },
    {
        path: '/admin/accounts',
        name: 'admin.accounts',
        component: AdminAccounts,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'admin')
    },
    {
        path: '/admin/rooms',
        name: 'admin.rooms',
        component: AdminRooms,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'admin')
    },
    {
        path: '/admin/reviews',
        name: 'admin.reviews',
        component: AdminReviews,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'admin')
    },
    {
        path: '/admin/rooms/add',
        name: 'admin.rooms.add',
        component: AddRoom
    },
    {
        path: '/admin/add-boarding-house',
        name: 'admin.boarding-house.add',
        component: AddBoardingHouse
    },
    {
        path: '/admin/edit-account/:id',
        name: 'EditAccount',
        component: EditAccount
    },
    {
        path: '/admin/add-user',
        name: 'admin.user.add',
        component: AddUser,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'admin')
    },
    {
        path: '/admin/boarding-houses',
        name: 'admin.boarding-houses',
        component: AdminBoardingHouse,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'admin')
    },
    {
        path: '/admin/boarding-houses/:id/edit',
        name: 'admin.boarding-houses.edit',
        component: EditBoardingHouse,
        props: true,
        meta: { requiresAuth: true, userType: 'admin' }
    },
    {
        path: '/admin/edit-room/:id',
        name: 'admin.room.edit',
        component: EditRoom,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'admin')
    },
    {
        path: '/admin/profile',
        name: 'admin.profile',
        component: AdminProfile,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'admin')
    },
    {
        path: '/admin/bookings',
        name: 'admin.bookings',
        component: AdminBookings,
        meta: { requiresAuth: true, userType: 'admin' }
    },
    {
        path: '/admin/revenue',
        name: 'admin.revenue',
        component: AdminRevenue,
        meta: { requiresAuth: true, userType: 'admin' }
    },
    {
        path: '/admin/payment-settings',
        name: 'admin.payment-settings',
        component: PaymentSettings
    },
    {
        path: '/admin/payment-management',
        name: 'admin.payments',
        component: PaymentManagement,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'admin')
    },
    {
        path: '/admin/tenants',
        name: 'admin.tenants',
        component: AdminTenants,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'admin')
    },

    // Landlord routes
    {
        path: '/landlord/dashboard',
        name: 'landlord.dashboard',
        component: LandlordPanel,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'landlord')
    },
    {
        path: '/landlord/boarding-houses',
        name: 'landlord.boarding-houses',
        component: LandlordBoardingHouses,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'landlord')
    },
    {
        path: '/landlord/bookings',
        name: 'landlord.bookings',
        component: LandlordBookings,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'landlord')
    },
    {
        path: '/landlord/reviews',
        name: 'landlord.reviews',
        component: LandlordReviews,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'landlord')
    },
    {
        path: '/landlord/revenue',
        name: 'landlord.revenue',
        component: LandlordRevenue,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'landlord')
    },
    {
        path: '/landlord/add-boarding-house',
        name: 'landlord.add-boarding-house',
        component: LandlordAddBoardingHouse,
        beforeEnter: (to, from, next) => guardRoute(to, from, next, 'landlord')
    },
    {
        path: '/landlord/add-room',
        name: 'landlord.add-room',
        component: LandlordAddRoom,
        meta: { requiresAuth: true, userType: 'landlord' }
    },
    {
        path: '/landlord/rooms',
        name: 'landlord.rooms',
        component: LandlordRooms,
        meta: { requiresAuth: true, userType: 'landlord' }
    },
    {
        path: '/landlord/rooms/:id/edit',
        name: 'landlord.rooms.edit',
        component: LandlordEditRoom,
        props: true,
        meta: { requiresAuth: true, userType: 'landlord' }
    },
    {
        path: '/landlord/boarding-houses/:id/edit',
        name: 'landlord.boarding-houses.edit',
        component: LandlordEditBoardingHouse,
        props: true,
        meta: { requiresAuth: true, userType: 'landlord' }
    },
    {
        path: '/landlord/profile',
        name: 'landlord.profile',
        component: LandlordProfile,
        meta: { requiresAuth: true, userType: 'landlord' }
    },
    {
        path: '/landlord/payment-settings',
        name: 'landlord.payment-settings',
        component: LandlordPaymentSettings
    },
    {
        path: '/landlord/tenants',
        name: 'landlord.tenants',
        component: LandlordTenants,
        meta: { requiresAuth: true, userType: 'landlord' }
    },
    {
        path: '/landlord/payment-management',
        name: 'landlord-payment-management',
        component: LandlordPaymentManagement,
        meta: { requiresAuth: true, userType: 'landlord' }
    },

    // Catch-all route for 404
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        redirect: '/'
    },
    {
        path: '/about_us',
        name: 'about-us',
        component: AboutUs
    },
    {
        path: '/terms',
        name: 'terms',
        component: TermsConditions
    },
    {
        path: '/legal',
        name: 'legal',
        component: LegalInformation
    },
    {
        path: '/privacy',
        name: 'privacy',
        component: PrivacyNotice
    },
    {
        path: '/personal-info',
        name: 'personal-info',
        component: PersonalInformation
    },
    {
        path: '/help',
        name: 'help',
        component: Help
    },
    {
        path: '/cyber-security',
        name: 'cyber-security',
        component: CyberSecurity
    },
    {
        path: '/how-it-works',
        name: 'how-it-works',
        component: HowItWorks
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Add this before creating the router
router.beforeEach((to, from, next) => {
    // Check if route requires auth
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!isAuthenticated()) {
            next({
                name: 'login',
                query: { redirect: to.fullPath }
            });
            return;
        }
    }

    // Check if route requires specific user type
    if (to.meta.userType) {
        if (!isAuthenticated(to.meta.userType)) {
            next({ name: 'login' });
            return;
        }
    }

    next();
});

export default router;
