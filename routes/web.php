<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LandlordController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BoardingHouseController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentQRCodeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ReceiptController;

// Public routes - no auth required
Route::prefix('user')->group(function () {
    // Room and boarding house routes
    Route::get('/rooms/{id}/details', [BoardingHouseController::class, 'getRoomDetails']);
    Route::get('/boarding-houses/{id}/details', [BoardingHouseController::class, 'getBoardingHouseDetails']);
    Route::get('/boarding-houses/{boardingHouseId}/payment-qr-codes/{paymentType}', 
        [PaymentQRCodeController::class, 'getQRCode'])->name('payment.qr-codes');
    
    // User routes
    Route::get('/profile', [UserController::class, 'getProfile']);
    Route::post('/verify-password', [UserController::class, 'verifyPassword']);
});

// Protected routes - require authentication
Route::prefix('user')->middleware(['web', 'auth'])->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BoardingHouseController::class, 'storeBooking']);
    Route::post('/bookings/create', [BookingController::class, 'store']);
    Route::post('/payments/create', [PaymentController::class, 'store']);
    Route::post('/payments/upload-proof', [PaymentController::class, 'uploadProof']);
    Route::get('/bookings/{booking_id}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking_id}/cancel', [BookingController::class, 'cancel']);
    Route::delete('/bookings/{booking_id}', [BookingController::class, 'destroy']);
    Route::get('/boarding-houses/{boardingHouseId}/payment-qr-codes/{paymentType}', 
        [PaymentQRCodeController::class, 'getQRCode']);
    
    // Review routes
    Route::post('/reviews', [BoardingHouseController::class, 'storeRoomReview']);
    Route::get('/rooms/{roomId}/reviews', [BoardingHouseController::class, 'getRoomReviews']);
    Route::put('/reviews/{id}', [BoardingHouseController::class, 'updateReview']);
    Route::delete('/reviews/{id}', [BoardingHouseController::class, 'deleteReview']);
});

// Admin Authentication Routes
Route::prefix('admin')->group(function () {
    Route::get('/admin_login', function () {
        return view('app');
    })->name('admin.login');
    
    Route::post('/admin_login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout']);
    
    Route::get('/dashboard', function () {
        return view('app');
    })->name('admin.dashboard');
});

// Landlord Authentication Routes
Route::post('/landlord/login', [LoginController::class, 'login'])->name('landlord.login');
Route::post('/landlord/logout', [LoginController::class, 'logout'])->name('landlord.logout');

// Landlord views and routes
Route::prefix('landlord')->group(function () {
    Route::get('/login', function () {
        return view('app');
    });
    Route::get('/dashboard', function () {
        return view('app');
    });
    Route::get('/rooms', function () {
        return view('app');
    });
    
    // Move these from api/landlord to here
    Route::get('/get-rooms', [LandlordController::class, 'getRooms']);
    Route::get('/get-dashboard-data/{userId}', [LandlordController::class, 'getLandlordDashboardData']);
    Route::get('/fetch-profile/{id}', [LandlordController::class, 'fetchProfile']);
    Route::post('/update-profile/{id}', [LandlordController::class, 'updateProfile']);
    Route::get('/rooms/add', function () {
        return view('app');
    });
    Route::get('/rooms/{id}/edit', function () {
        return view('app');
    });
    Route::get('/rooms/{id}', function () {
        return view('app');
    });
    Route::get('/add-room', function () {
        return view('app');
    });
    Route::get('/get-boarding-houses', [LandlordController::class, 'getBoardingHouses']);
    Route::get('/rooms', function () {
        return view('app');
    });
    Route::get('/get-room/{id}', [LandlordController::class, 'getRoom']);
    Route::post('/update-room/{id}', [LandlordController::class, 'updateRoom']);
    Route::post('/add-boarding-house', [LandlordController::class, 'addBoardingHouse']);
    Route::get('/add-boarding-house', function () {
        return view('app');
    });
    Route::post('/add-room', [LandlordController::class, 'addRoom']);
    Route::get('/add-room', function () {
        return view('app');
    });
    Route::get('/boarding-houses/{id}', [LandlordController::class, 'getBoardingHouse']);
    Route::put('/boarding-houses/{id}', [LandlordController::class, 'updateBoardingHouse']);
    Route::get('/profile/{id}', [LandlordController::class, 'getProfile']);
    Route::post('/logout', [LandlordController::class, 'logout']);
    Route::get('/get-dashboard-data/{userId}', [LandlordController::class, 'getLandlordDashboardData']);
    Route::get('/get-bookings', [LandlordController::class, 'getBookings']);
    Route::get('/get-reviews', [LandlordController::class, 'getReviews']);
    Route::get('/revenue/{userId}', [LandlordController::class, 'getRevenue']);
    Route::post('/update-boarding-house/{id}', [LandlordController::class, 'updateBoardingHouse']);
    Route::get('/get-boarding-house/{id}', [LandlordController::class, 'getBoardingHouse']);
    Route::middleware(['auth'])->group(function () {
        Route::get('/payment-qr-codes', [LandlordController::class, 'getPaymentQRCodes']);
        Route::post('/payment-qr-codes', [LandlordController::class, 'storePaymentQRCode']);
        Route::delete('/payment-qr-codes/{id}', [LandlordController::class, 'deletePaymentQRCode']);
    });
    Route::delete('/delete-boarding-house/{id}', [LandlordController::class, 'deleteBoardingHouse']);
    Route::delete('/delete-room/{id}', [LandlordController::class, 'deleteRoom']);
    Route::post('/bookings/{id}/cancel', [LandlordController::class, 'cancelBooking']);
    Route::post('/bookings/{id}/confirm', [LandlordController::class, 'confirmBooking']);
    Route::get('/dashboard-data', [LandlordController::class, 'getDashboardData']);
    Route::get('/get-tenants/{userId}', [LandlordController::class, 'getTenants']);
    Route::delete('/delete-tenant/{userId}/{tenantId}', [LandlordController::class, 'deleteTenant']);
    Route::get('/payments', [LandlordController::class, 'getPayments']);
    Route::patch('/payments/{id}/status', [LandlordController::class, 'updatePaymentStatus']);
    Route::get('/payment-stats', [LandlordController::class, 'getPaymentStats']);
    Route::post('/update-payment/{id}', [LandlordController::class, 'updatePaymentStatus']);
    Route::get('/recent-activities', [LandlordController::class, 'getRecentActivities']);
    Route::get('/get-reviews', [LandlordController::class, 'getReviews']);
    Route::get('/fetch-boarding-houses', [LandlordController::class, 'getBoardingHouses']);
    Route::post('/delete-review/{id}', [LandlordController::class, 'deleteReview']);
    Route::post('/report-review/{id}', [LandlordController::class, 'reportReview'])
        ->middleware(['auth', 'landlord']);
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::get('/boarding-houses', function () {
        return view('app');
    });
    Route::get('/boarding-houses/{id}/edit', function () {
        return view('app');
    });
    Route::get('/get-boarding-houses', [AdminController::class, 'getBoardingHouses']);
    Route::get('/get-boarding-house/{id}', [AdminController::class, 'getBoardingHouse']);
    Route::match(['post', 'put'], '/boarding-houses/{id}', [AdminController::class, 'updateBoardingHouse']);
    
    Route::get('/get-rooms', [AdminController::class, 'getRooms']);
    
    Route::get('/fetch-boarding-houses', [AdminController::class, 'fetchBoardingHouses']);
    Route::get('/fetch-accounts', [AdminController::class, 'fetchAccounts']);
    Route::delete('/delete-account/{id}', [AdminController::class, 'deleteAccount']);
    Route::get('/edit-account/{id}', [AdminController::class, 'editAccount']); // Matches fetchAccountDetails() axios call
    Route::put('/update-account/{id}', [AdminController::class, 'updateAccount'])->name('admin.update-account');
    Route::get('/fetch-account/{id}', [AdminController::class, 'fetchAccount']);
    Route::get('/fetch-rooms', [AdminController::class, 'fetchRooms']);
    Route::get('/fetch-boarding-house/{id}', [AdminController::class, 'fetchBoardingHouse']);
    Route::put('/update-boarding-house/{id}', [AdminController::class, 'updateBoardingHouse']);
    Route::get('/fetch-room/{id}', [AdminController::class, 'fetchRoom']);
    Route::post('/update-room/{id}', [AdminController::class, 'updateRoom']);
    Route::get('/edit-room/{id}', function () {
        return view('app');
    });
    
    // Admin views - make sure all paths match router.js
    Route::get('/admin_login', function () {
        return view('app');
    });
    Route::get('/', function () {
        return view('app');
    });
    Route::get('/accounts', function () {
        return view('app');
    });
    Route::get('/edit-account/{id}', function () {
        return view('app');
    });
    
    // Add these routes for rooms
    Route::get('/rooms/add', function () {
        return view('app');
    });
    Route::get('/add-boarding-house', function () {
        return view('app');
    });
    Route::get('/rooms', function () {
        return view('app');
    });
    
    Route::get('/add-user', function () {
        return view('app');
    });
    
    Route::post('/add-user', [AdminController::class, 'addUser']);
    Route::post('/add-boarding-house', [AdminController::class, 'addBoardingHouse']);
    Route::post('/add-room', [AdminController::class, 'addRoom']);
    Route::post('/update-profile/{id}', [AdminController::class, 'updateProfile']);
    Route::get('/bookings', function () {
        return view('app');
    });
    Route::get('/fetch-bookings', [AdminController::class, 'fetchBookings']);
    Route::get('/revenue', function () {
        return view('app');
    });
    Route::get('/fetch-revenue', [AdminController::class, 'fetchRevenue']);

    // Dashboard and activities routes
    Route::get('/get-dashboard-data', [AdminController::class, 'getDashboardData']);
    
    // Account routes
    Route::post('/add-user', [AdminController::class, 'addUser']);
    Route::put('/update-user/{id}', [AdminController::class, 'updateUser']);
    
    // Boarding house routes
    Route::post('/add-boarding-house', [AdminController::class, 'addBoardingHouse']);
    Route::put('/update-boarding-house/{id}', [AdminController::class, 'updateBoardingHouse']);
    
    // Room routes
    Route::post('/add-room', [AdminController::class, 'addRoom']);
    Route::put('/update-room/{id}', [AdminController::class, 'updateRoom']);
    
    // Profile routes
    Route::put('/update-profile/{id}', [AdminController::class, 'updateProfile']);
    
    // Review routes
    Route::get('/get-reviews', [AdminController::class, 'getReviews']);
    Route::get('/get-total-reviews', [AdminController::class, 'getTotalReviews']);
    Route::delete('/delete-review/{id}', [AdminController::class, 'deleteReview']);
    
    // Booking routes
    Route::put('/update-booking/{id}', [AdminController::class, 'updateBooking']);
    
    // Revenue routes
    Route::post('/add-revenue', [AdminController::class, 'addRevenue']);
    
    // Fetch routes
    Route::get('/fetch-rooms', [AdminController::class, 'getRooms']);
    Route::get('/fetch-boarding-houses', [AdminController::class, 'getBoardingHouses']);
    Route::get('/fetch-bookings', [AdminController::class, 'getBookings']);
    Route::get('/fetch-revenue', [AdminController::class, 'fetchRevenue']);
    Route::get('/get-bookings', [AdminController::class, 'getBookings']);
    Route::get('/get-revenue', [AdminController::class, 'getRevenue']);
    Route::post('/confirm-booking/{id}', [AdminController::class, 'confirmBooking']);
    Route::post('/cancel-booking/{id}', [AdminController::class, 'cancelBooking']);
    Route::get('/boarding-houses', function () {
        return view('app');
    });
    Route::get('/get-boarding-houses', [AdminController::class, 'getBoardingHouses']);
    Route::get('/boarding-houses/{id}/edit', function () {
        return view('app');
    });
    Route::get('/get-boarding-house/{id}', [AdminController::class, 'getBoardingHouse']);
    Route::put('/update-boarding-house/{id}', [AdminController::class, 'updateBoardingHouse']);
    Route::get('/get-boarding-houses', [AdminController::class, 'getBoardingHouses']);
    Route::get('/get-boarding-house/{id}', [AdminController::class, 'getBoardingHouse']);
    Route::put('/update-boarding-house/{id}', [AdminController::class, 'updateBoardingHouse']);
    Route::get('/boarding-houses', function () {
        return view('app');
    });
    Route::get('/get-boarding-houses', [AdminController::class, 'getBoardingHouses']);
    Route::get('/boarding-house/{id}', [AdminController::class, 'getBoardingHouse']);
    Route::put('/boarding-house/{id}', [AdminController::class, 'updateBoardingHouse']);

    // Support Routes
    Route::get('/support-requests', [AdminController::class, 'getSupportRequests']);
    Route::put('/support-requests/{id}/status', [AdminController::class, 'updateSupportStatus']);
    Route::post('/support-requests/{id}/respond', [AdminController::class, 'respondToSupport']);
    Route::delete('/support-requests/{id}', [AdminController::class, 'deleteSupportRequest']);
    Route::delete('/boarding-houses/{id}', [AdminController::class, 'deleteBoardingHouse']);
    Route::get('/payment-qr-codes', [AdminController::class, 'getPaymentQRCodes']);
    Route::post('/payment-qr-codes', [AdminController::class, 'storePaymentQRCode']);
    Route::delete('/payment-qr-codes/{id}', [AdminController::class, 'deletePaymentQRCode']);
    Route::post('/confirm-booking/{bookingId}', [BookingController::class, 'confirmBooking']);
    Route::post('/cancel-booking/{bookingId}', [BookingController::class, 'cancelBooking']);
    Route::delete('/delete-room/{id}', [AdminController::class, 'deleteRoom']);
    Route::get('/tenant-stats', [AdminController::class, 'getTenantStats']);
    Route::get('/tenants', function () {
        return view('app');
    })->name('admin.tenants.view');
    
    Route::get('/tenants/data', [AdminController::class, 'getTenants'])->name('admin.tenants.data');
    Route::delete('/tenants/{id}', [AdminController::class, 'deleteTenant'])
        ->name('admin.tenants.delete');

    // Payment Management Routes
    Route::get('/payments', [AdminController::class, 'fetchPayments']);
    Route::patch('/payments/{paymentId}', [AdminController::class, 'updatePaymentStatus']);

    // Payment Management Routes - without auth middleware
    Route::get('/fetch-payments', [AdminController::class, 'fetchPayments'])->withoutMiddleware(['auth', 'admin']);
    Route::patch('/update-payment/{paymentId}', [AdminController::class, 'updatePaymentStatus'])->withoutMiddleware(['auth', 'admin']);

    // Add this route for bookings
    Route::get('/get-bookings', [AdminController::class, 'getBookings'])->name('admin.bookings');

    // Add this route for recent activities
    Route::get('/recent-activities', [AdminController::class, 'getRecentActivities']);

    // Add these routes in the admin group
    Route::put('/admin/reviews/{id}', [AdminController::class, 'updateReview']);
    Route::delete('/admin/reviews/{id}', [AdminController::class, 'deleteReview']);

    // Add this route with your other admin routes
    Route::get('/admin/rooms/{roomId}/reviews', [AdminController::class, 'fetchRoomReviews']);

    // Review management routes
    Route::get('/rooms/{roomId}/reviews', [AdminController::class, 'fetchRoomReviews']);
    Route::post('/reviews/room', [AdminController::class, 'storeRoomReview']);
    Route::put('/reviews/update/{id}', [AdminController::class, 'updateReview']);
    Route::delete('/reviews/delete/{id}', [AdminController::class, 'deleteReview']);

    // Boarding house selection routes
    Route::get('/boarding-houses/list', [AdminController::class, 'getBoardingHouses']);
    Route::get('/boarding-houses/{id}/details', [AdminController::class, 'getBoardingHouseDetails']);
    Route::get('/boarding-houses/{id}/rooms', [AdminController::class, 'getBoardingHouseRooms']);

    // Add these routes in your admin routes group
    Route::prefix('admin')->group(function () {
        // ... existing routes ...
        
        Route::get('/reported-users', [AdminController::class, 'getReportedUsers']);
        Route::patch('/reported-users/{id}/status', [AdminController::class, 'updateReportStatus']);
        Route::delete('/reported-users/{id}', [AdminController::class, 'dismissReport']);
    });
});

// Authentication Routes
Route::post('/register', [UserController::class, 'register'])->name('register');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// User views
Route::prefix('user')->middleware(['web', 'auth'])->group(function () {
    Route::get('/home', function () {
        return view('app');
    });
    // ... other user routes
    
    // Profile routes
    Route::get('/profile', [UserController::class, 'getProfile']);
    Route::get('/fetch-profile/{id}', [UserController::class, 'fetchProfile']);
    Route::post('/update-profile/{id}', [UserController::class, 'updateProfile']);
    Route::post('/update-profile-picture', [UserController::class, 'updateProfilePicture']);
    Route::put('/change-password/{id}', [UserController::class, 'changePassword']);
    
    Route::get('/rooms/{id}', function () {
        return view('app');
    });
    
    Route::get('/rooms/{id}/list', [BoardingHouseController::class, 'getBoardingHouseRooms']);
    
    Route::get('/boarding-houses/{id}/details', [BoardingHouseController::class, 'getBoardingHouseDetails']);
    Route::get('/rooms/{id}/details', [BoardingHouseController::class, 'getRoomDetails']);
    Route::post('/bookings', [BoardingHouseController::class, 'storeBooking']);
    
    // QR Code routes
    Route::get('/user/boarding-houses/{boardingHouseId}/payment-qr-codes/{paymentType}', 
        [PaymentQRCodeController::class, 'getQRCode']);
    Route::post('/verify-password', [UserController::class, 'verifyPassword']);
    
    // Payment routes
    Route::post('/payments/create', [PaymentController::class, 'store']);
    Route::post('/payments/upload-proof', [PaymentController::class, 'uploadProof']);
});

// Notification Routes - Add before the catch-all route
Route::prefix('notifications')->group(function () {
    Route::get('/{userId}', [NotificationController::class, 'getNotifications'])->name('notifications.get');
    Route::post('/create', [NotificationController::class, 'createNotification'])->name('notifications.create');
    Route::post('/mark-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/mark-all-read/{userId}', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/{id}', [NotificationController::class, 'deleteNotification'])->name('notifications.delete');
});

// Add this route outside of any prefix group, before the catch-all route
Route::get('/boarding-houses/available', [BoardingHouseController::class, 'getAvailableBoardingHouses']);

// Keep the catch-all route at the very end
Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');

Route::get('/user/fetch-profile/{id}', [UserController::class, 'fetchProfile']);

// User Profile Routes
Route::prefix('user')->group(function () {
    Route::post('/profile-picture', [UserController::class, 'updateProfilePicture']);
    Route::put('/profile/{id}', [UserController::class, 'updateProfile']);
    Route::put('/change-password/{id}', [UserController::class, 'changePassword']);
    Route::get('/fetch-profile/{id}', [UserController::class, 'fetchProfile']);
});

// Support Routes
Route::prefix('support')->group(function () {
    Route::post('/create', [SupportController::class, 'create']);
    Route::get('/requests', [SupportController::class, 'getAllRequests']);
    Route::put('/requests/{id}/status', [SupportController::class, 'updateStatus']);
});

// Admin Support Routes
Route::prefix('admin')->middleware(['web'])->group(function () {
    Route::get('/support-requests', [AdminController::class, 'getSupportRequests']);
    Route::put('/support-requests/{id}/status', [AdminController::class, 'updateSupportStatus']);
    Route::post('/support-requests/{id}/respond', [AdminController::class, 'respondToSupport']);
    Route::delete('/support-requests/{id}', [AdminController::class, 'deleteSupportRequest']);
});

// Boarding House Routes
Route::get('/boarding-houses/available', [BoardingHouseController::class, 'getAvailableBoardingHouses']);
// Add these routes in the public section, before the catch-all route
Route::get('/boarding-houses/{id}/reviews', [BoardingHouseController::class, 'getBoardingHouseReviews']);
Route::get('/boarding-houses/{id}', [BoardingHouseController::class, 'getBoardingHouse']);
Route::post('/boarding-houses/reviews', [BoardingHouseController::class, 'storeReview']);

// Add this BEFORE the catch-all route and outside any prefix groups
Route::get('/boarding-houses/{id}/rooms', [BoardingHouseController::class, 'getBoardingHouseRooms']);

// User Booking Routes
Route::prefix('user')->group(function () {
    Route::post('/validate-booking-dates', [BookingController::class, 'validateBookingDates']);
    Route::post('/bookings', [BookingController::class, 'store']);
});

Route::prefix('user')->group(function () {
    Route::get('/profile', [UserController::class, 'getProfile']);
    // ... other routes
});

Route::prefix('user')->middleware(['auth'])->group(function () {
    Route::post('/verify-password', [UserController::class, 'verifyPassword']);
});

// Public routes for viewing rooms and boarding houses
Route::get('/user/rooms/{id}/details', [BoardingHouseController::class, 'getRoomDetails']);
Route::get('/user/boarding-houses/{id}/details', [BoardingHouseController::class, 'getBoardingHouseDetails']);
Route::get('/user/rooms/{id}/list', [BoardingHouseController::class, 'getBoardingHouseRooms']);

// Public routes
Route::get('/user/profile', [UserController::class, 'getProfile']);
Route::get('/user/rooms/{id}/details', [BoardingHouseController::class, 'getRoomDetails']);
Route::get('/user/boarding-houses/{id}/details', [BoardingHouseController::class, 'getBoardingHouseDetails']);

// Public routes - Add these before any middleware groups
Route::prefix('user')->group(function () {
    Route::get('/rooms/{id}/details', [BoardingHouseController::class, 'getRoomDetails']);
    Route::get('/boarding-houses/{id}/details', [BoardingHouseController::class, 'getBoardingHouseDetails']);
    Route::get('/boarding-houses/{boardingHouseId}/payment-qr-codes/{paymentType}', 
        [PaymentQRCodeController::class, 'getQRCode']);
});

// Protected routes - Keep these in the authenticated group
Route::prefix('user')->middleware(['auth'])->group(function () {
    Route::post('/verify-password', [UserController::class, 'verifyPassword']);
    Route::post('/payments/create', [PaymentController::class, 'store']);
    Route::post('/payments/upload-proof', [PaymentController::class, 'uploadProof']);
});

// Add this route for fetching room details
Route::get('/landlord/rooms/{id}', [\App\Http\Controllers\RoomController::class, 'getRoomDetails'])->name('landlord.room.details');

Route::post('/notifications/create', [NotificationController::class, 'createNotification'])->name('notifications.create');

// Update the dashboard data route
Route::prefix('landlord')->middleware(['auth'])->group(function () {
    // ... other routes ...
    Route::get('/get-dashboard-data', [LandlordController::class, 'getDashboardData']);
    // ... other routes ...
});

Route::get('/landlord/get-dashboard-data', [LandlordController::class, 'getDashboardData']);

Route::prefix('admin')->group(function () {
    // ... other routes ...
    Route::get('/tenants', function () {
        return view('app');
    })->name('admin.tenants.view');
    
    Route::get('/tenants/data', [AdminController::class, 'getTenants'])->name('admin.tenants.data');
    Route::get('/tenant-stats', [AdminController::class, 'getTenantStats'])->name('admin.tenants.stats');
    
    // Remove duplicate tenant routes if they exist
}); 

Route::prefix('admin')->middleware(['web', 'auth', 'admin'])->group(function () {
    // ... other admin routes ...
    Route::delete('/delete-booking/{bookingId}', [AdminBookingController::class, 'deleteBooking']);
});

Route::get('/admin/get-landlord-boarding-houses', [AdminController::class, 'getLandlordBoardingHouses']);

// Admin Payment Management Routes
Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
    // ... other admin routes ...

    // Update these payment routes
    Route::get('/payments', [AdminController::class, 'fetchPayments'])->name('admin.payments');
    Route::patch('/payments/{paymentId}', [AdminController::class, 'updatePaymentStatus'])->name('admin.payments.update');
    
    // Add this new route to handle the view
    Route::get('/payment-management', function () {
        return view('app');
    })->name('admin.payment-management');
});

Route::get('/admin/fetch-payments', [AdminController::class, 'fetchPayments']);

Route::post('/user/payments/create', [PaymentController::class, 'store']);
Route::put('/user/payments/{id}', [PaymentController::class, 'update']);

Route::put('/update-payment/{paymentId}', [AdminController::class, 'updatePaymentStatus']);

Route::get('/bookings/{booking}/payment-status', [PaymentController::class, 'getPaymentStatus']);
Route::patch('/payments/{payment}/status', [PaymentController::class, 'updatePaymentStatus']);

Route::get('/user/boarding-houses/{id}', 'BoardingHouseController@show');

Route::get('/admin/recent-activities', [AdminController::class, 'getRecentActivities']);

Route::middleware(['auth', 'admin'])->group(function () {
    // ... existing routes ...
    Route::get('/admin/recent-activities', 'AdminController@getRecentActivities');
});

Route::get('/landlord/payment-stats', [LandlordController::class, 'getPaymentStats']);

Route::get('/admin/recent-activities', [AdminController::class, 'getRecentActivities']);

Route::get('/landlord/recent-activities', [LandlordController::class, 'getRecentActivities']);

// Add this route in the public section
Route::get('/search-rooms', [BoardingHouseController::class, 'searchRooms']);

// Add this route for BHList
Route::get('/bhlist', function () {
    return view('app');
})->name('bhlist');

// Update these routes to handle the search functionality
Route::get('/search-rooms', [BoardingHouseController::class, 'searchRooms']);
Route::get('/boarding-houses/available', [BoardingHouseController::class, 'getAvailableBoardingHouses']);

// Add this with your other review routes
Route::post('/boarding-houses/room-reviews', [BoardingHouseController::class, 'storeRoomReview']);

Route::get('/admin/recent-activities', [AdminController::class, 'getRecentActivities']);

// Landlord Routes
Route::prefix('landlord')->group(function () {
    // ... other routes ...
    
    // Recent Activities Route - without sanctum middleware
    Route::get('/recent-activities', [LandlordController::class, 'getRecentActivities']);
});

// Add this route to serve storage files
Route::get('/storage/{path}', function($path) {
    return Storage::response('public/' . $path);
})->where('path', '.*');

// Add this route if it's not already defined
Route::post('/user/reviews/{id}', [BoardingHouseController::class, 'updateReview'])->name('user.reviews.update');

Route::get('/admin/reported-reviews', [AdminController::class, 'getReportedReviews']);
Route::get('/admin/reported-reviews/stats', [AdminController::class, 'getReportedReviewsStats']);
Route::patch('/admin/reported-reviews/{id}/status', [AdminController::class, 'updateReportStatus']);
Route::delete('/admin/reported-reviews/{id}', [AdminController::class, 'dismissReport']);
Route::get('/admin/review-reports', function () {
    return view('app');
});

// Add this route before the catch-all route
Route::get('/about_us', function () {
    return view('app');
});

// Add these routes in the appropriate section
Route::post('/generate-receipt/{paymentId}', [ReceiptController::class, 'generateReceipt'])
    ->name('generate.receipt');

Route::get('/download-receipt/{paymentId}', [ReceiptController::class, 'downloadReceipt'])
    ->name('download.receipt');

// Logout routes
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::post('/landlord/logout', [LandlordController::class, 'logout'])->name('landlord.logout');