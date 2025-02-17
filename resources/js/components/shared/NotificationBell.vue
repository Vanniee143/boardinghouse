<template>
  <div class="notification-container">
    <div class="notification-bell" @click="toggleNotifications">
      <i class="fas fa-bell"></i>
      <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
    </div>
    
    <div v-if="showNotifications" class="notification-dropdown">
      <div class="notification-header">
        <h3>Notifications</h3>
        <button v-if="notifications.length > 0" @click="markAllAsRead" class="mark-all-read">
          Mark all as read
        </button>
      </div>

      <div class="notification-list">
        <div v-if="notifications.length === 0" class="no-notifications">
          No notifications
        </div>
        
        <div v-else v-for="notification in notifications" 
             :key="notification.id" 
             :class="['notification-item', { unread: !notification.read }]"
             @click="handleNotificationClick(notification)">
          <div class="notification-icon" :class="notification.type">
            <i :class="getNotificationIcon(notification.type)"></i>
          </div>
          <div class="notification-content">
            <p class="notification-text">{{ notification.message }}</p>
            <span class="notification-time">{{ formatTime(notification.created_at) }}</span>
          </div>
          <button class="delete-notification" @click.stop="deleteNotification(notification.id)">
            ×
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'NotificationBell',
  data() {
    return {
      showNotifications: false,
      notifications: [],
      unreadCount: 0
    }
  },
  methods: {
    toggleNotifications() {
      this.showNotifications = !this.showNotifications;
      if (this.showNotifications) {
        this.fetchNotifications();
      }
    },
    async fetchNotifications() {
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          console.error('No user ID found in localStorage');
          return;
        }

        console.log('Fetching notifications for user:', userId);
        
        // Add error handling and debugging
        try {
          const response = await axios.get(`/notifications/${userId}`);
          console.log('Full response:', response);
          
          if (response.data && response.data.status === 'success') {
            this.notifications = response.data.data;
            this.unreadCount = this.notifications.filter(n => !n.read).length;
            console.log('Notifications loaded:', this.notifications.length);
          } else {
            console.error('Invalid response format:', response.data);
          }
        } catch (error) {
          if (error.response) {
            // The request was made and the server responded with a status code
            // that falls out of the range of 2xx
            console.error('Error response:', error.response.data);
            console.error('Error status:', error.response.status);
            console.error('Error headers:', error.response.headers);
          } else if (error.request) {
            // The request was made but no response was received
            console.error('No response received:', error.request);
          } else {
            // Something happened in setting up the request that triggered an Error
            console.error('Error setting up request:', error.message);
          }
        }
      } catch (error) {
        console.error('Error in fetchNotifications:', error);
      }
    },
    async markAllAsRead() {
      try {
        const userId = localStorage.getItem('userId');
        await axios.post(`/notifications/mark-all-read/${userId}`);
        this.notifications = this.notifications.map(n => ({ ...n, read: true }));
        this.unreadCount = 0;
      } catch (error) {
        console.error('Error marking notifications as read:', error);
      }
    },
    async handleNotificationClick(notification) {
      if (!notification.read) {
        try {
          await axios.post(`/notifications/mark-read/${notification.id}`);
          notification.read = true;
          this.unreadCount = Math.max(0, this.unreadCount - 1);
        } catch (error) {
          console.error('Error marking notification as read:', error);
        }
      }
      
      // Handle receipt download
      if (notification.has_attachment && notification.attachment_type === 'receipt') {
        window.open(notification.link, '_blank');
        return;
      }
      
      // Handle other notification types...
      if (notification.type === 'payment' && notification.link) {
        window.open(notification.link, '_blank');
      } else if (notification.link) {
        this.$router.push(notification.link);
      }
    },
    async deleteNotification(id) {
      try {
        await axios.delete(`/notifications/${id}`);
        this.notifications = this.notifications.filter(n => n.id !== id);
        this.unreadCount = this.notifications.filter(n => !n.read).length;
      } catch (error) {
        console.error('Error deleting notification:', error);
      }
    },
    getNotificationIcon(type) {
      switch (type) {
        case 'payment':
          return 'fas fa-money-bill-wave';
        case 'booking':
          if (this.message?.toLowerCase().includes('cancelled')) {
            return 'fas fa-times-circle';
          } else if (this.message?.toLowerCase().includes('confirmed')) {
            return 'fas fa-check-circle';
          }
          return 'fas fa-calendar-check';
        case 'support':
          return 'fas fa-headset';
        case 'support_response':
          return 'fas fa-reply';
        case 'review':
          return 'fas fa-star';
        case 'system':
          return 'fas fa-info-circle';
        default:
          return 'fas fa-bell';
      }
    },
    formatTime(date) {
      const now = new Date();
      const notificationDate = new Date(date);
      const diffInMinutes = Math.floor((now - notificationDate) / 60000);
      
      if (diffInMinutes < 60) {
        return `${diffInMinutes}m ago`;
      }
      
      const diffInHours = Math.floor(diffInMinutes / 60);
      if (diffInHours < 24) {
        return `${diffInHours}h ago`;
      }
      
      const diffInDays = Math.floor(diffInHours / 24);
      if (diffInDays < 7) {
        return `${diffInDays}d ago`;
      }
      
      return notificationDate.toLocaleDateString();
    },
    getNotificationColor(notification) {
      if (notification.type === 'support') {
        switch (notification.message.toLowerCase()) {
          case /pending/.test(notification.message):
            return 'pending';
          case /processing|progress/.test(notification.message):
            return 'processing';
          case /resolved|complete/.test(notification.message):
            return 'resolved';
          default:
            return '';
        }
      }
      return notification.type;
    }
  },
  mounted() {
    console.log('NotificationBell mounted');
    console.log('User ID:', localStorage.getItem('userId'));
    
    // Initial fetch
    this.fetchNotifications();
    
    // Set up polling for new notifications
    this.notificationInterval = setInterval(() => {
      if (!this.showNotifications) {
        console.log('Polling for new notifications');
        this.fetchNotifications();
      }
    }, 30000);
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!this.$el.contains(e.target)) {
        this.showNotifications = false;
      }
    });
  },
  beforeDestroy() {
    clearInterval(this.notificationInterval);
  }
}
</script>

<style scoped>
.notification-container {
  position: relative;
  margin: 0 1rem;
}

.notification-bell {
  cursor: pointer;
  padding: 0.5rem;
  position: relative;
  color: #794646;
}

.notification-badge {
  position: absolute;
  top: 0;
  right: 0;
  background: #ef4444;
  color: white;
  border-radius: 50%;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  min-width: 1.5rem;
  text-align: center;
}

.notification-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  width: 320px;
  max-height: 400px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  overflow-y: auto;
}

.notification-header {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notification-header h3 {
  margin: 0;
  color: #374151;
  font-size: 1rem;
}

.mark-all-read {
  background: none;
  border: none;
  color: #794646;
  font-size: 0.875rem;
  cursor: pointer;
}

.notification-list {
  padding: 0.5rem;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  padding: 0.75rem;
  border-radius: 6px;
  margin-bottom: 0.5rem;
  cursor: pointer;
  transition: background-color 0.2s;
  position: relative;
  border-left: 3px solid transparent;
}

.notification-item:hover {
  background: #f9fafb;
}

.notification-item.unread {
  background: #f3f4f6;
}

.notification-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.75rem;
  flex-shrink: 0;
}

.notification-icon.booking { background: #dbeafe; color: #2563eb; }
.notification-icon.support { background: #fef3c7; color: #d97706; }
.notification-icon.review { background: #fee2e2; color: #dc2626; }
.notification-icon.system { background: #e0e7ff; color: #4f46e5; }
.notification-icon.support_response { background: #818cf8; color: white; }

.notification-content {
  flex: 1;
}

.notification-text {
  margin: 0;
  color: #374151;
  font-size: 0.875rem;
  line-height: 1.25;
}

.notification-time {
  font-size: 0.75rem;
  color: #6b7280;
}

.delete-notification {
  background: none;
  border: none;
  color: #9ca3af;
  font-size: 1.25rem;
  padding: 0.25rem;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.2s;
}

.notification-item:hover .delete-notification {
  opacity: 1;
}

.no-notifications {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
  font-size: 0.875rem;
}

.notification-item.support,
.notification-item.support_response {
  border-left: 3px solid #818cf8;
}

.notification-item.pending {
  border-left: 3px solid #f59e0b;
}

.notification-item.processing {
  border-left: 3px solid #3b82f6;
}

.notification-item.resolved {
  border-left: 3px solid #10b981;
}

.notification-icon.pending { 
  background: #fef3c7; 
  color: #d97706; 
}

.notification-icon.processing { 
  background: #dbeafe; 
  color: #2563eb; 
}

.notification-icon.resolved { 
  background: #d1fae5; 
  color: #059669; 
}

.notification-icon.booking {
    background: #dbeafe;
    color: #2563eb;
}

.notification-item.booking {
    border-left: 3px solid #2563eb;
}

/* Add different styles for booking status */
.notification-item.booking.pending {
    border-left-color: #f59e0b;
}

.notification-item.booking.confirmed {
    border-left-color: #10b981;
}

.notification-item.booking.cancelled {
    border-left-color: #ef4444;
}

.notification-item.booking.cancelled {
    border-left: 3px solid #ef4444;
}

.notification-icon.booking.cancelled {
    background: #fee2e2;
    color: #dc2626;
}

.notification-icon.payment {
    background: #ecfdf5;
    color: #059669;
}

.notification-item.payment {
    border-left: 3px solid #059669;
}

/* Add animation for new payment notifications */
@keyframes highlight {
    0% { background-color: #ecfdf5; }
    100% { background-color: transparent; }
}

.notification-item.payment.unread {
    animation: highlight 2s ease-out;
}

.notification-item.booking.confirmed {
  border-left: 3px solid #10b981;
}

.notification-item.booking.cancelled {
  border-left: 3px solid #ef4444;
}

.notification-icon.booking.confirmed {
  background: #d1fae5;
  color: #059669;
}

.notification-icon.booking.cancelled {
  background: #fee2e2;
  color: #dc2626;
}
</style> 