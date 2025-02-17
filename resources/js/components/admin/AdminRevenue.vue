<template>
  <div class="admin-panel">
    <div class="navbars">
      <router-link to="/admin" class="logo-link">
        <img src="@/assets/images/image.png" alt="logo" class="logos">
      </router-link>
      <router-link to="/admin" class="nl">
        <h5>SBH BOOKING</h5>
      </router-link>

      <div class="profile-section">
        <div class="admin-profile">
          <img 
            :src="getProfilePicture(adminProfile.profile_picture)"
            alt="profile" 
            class="profile-icon"
            @error="handleImageError"
          />
          <span class="admin-profile-name">{{ adminName }}</span>
        </div>
      </div>

      <div class="menu-container">
        <img src="@/assets/images/Menu.png" alt="menu" class="menuimgg" id="menuImage">
        <select class="Menu" id="menuSelect" @change="navigateMenu">
          <option value="#" disabled selected hidden>Menu</option>
          <option value="/admin/accounts">Accounts</option>
          <option value="/admin/profile">Profile Settings</option>
          <option value="/admin/admin_login">Logout</option>
        </select>
      </div>
    </div>

    <div class="overall">
      <div class="home">
        <h4 class="helper">REVENUE</h4>
      </div>

      <div class="revenue-wrapper">
        <div class="filters">
          <select v-model="selectedHouse" class="filter-select" @change="calculateRevenue">
            <option value="all">All Boarding Houses</option>
            <option v-for="house in boardingHouses" :key="house.id" :value="house.id">
              {{ house.name }}
            </option>
          </select>

          <select v-model="selectedPeriod" class="filter-select">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>

          <div class="date-range">
            <input type="date" v-model="startDate" class="date-input" :max="endDate">
            <span class="date-separator">to</span>
            <input type="date" v-model="endDate" class="date-input" :min="startDate">
          </div>
        </div>

        <div class="revenue-stats">
          <div class="stat-card total-revenue">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
              <h3>Total Revenue</h3>
              <p class="amount">₱{{ formatNumber(totalRevenue) }}</p>
            </div>
          </div>

          <div class="stat-card bookings">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
              <h3>Total Bookings</h3>
              <p class="amount">{{ totalBookings }}</p>
            </div>
          </div>

          <div class="stat-card average">
            <div class="stat-icon">📈</div>
            <div class="stat-info">
              <h3>Average per Booking</h3>
              <p class="amount">₱{{ formatNumber(averagePerBooking) }}</p>
            </div>
          </div>
        </div>

        <div class="revenue-chart">
          <canvas ref="revenueChart"></canvas>
        </div>

        <div class="revenue-table">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Boarding House</th>
                <th>Room</th>
                <th>Guest</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Duration</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="transaction in filteredTransactions" :key="transaction.id">
                <td>{{ formatDate(transaction.date) }}</td>
                <td>{{ transaction.house_name }}</td>
                <td>{{ transaction.room_name }}</td>
                <td>{{ transaction.guest_name }}</td>
                <td>{{ formatDate(transaction.check_in_date) }}</td>
                <td>{{ formatDate(transaction.check_out_date) }}</td>
                <td>{{ transaction.duration }} days</td>
                <td>₱{{ formatNumber(transaction.amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Chart from 'chart.js/auto';
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'AdminRevenue',
  data() {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth() + 1;
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    return {
      adminName: localStorage.getItem('userName') || 'Admin',
      adminProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId'),
        name: localStorage.getItem('userName'),
        email: localStorage.getItem('userEmail')
      },
      defaultProfilePic,
      revenueData: {
        bookingStats: {
          total: 0,
          confirmed: 0,
          pending: 0,
          cancelled: 0
        },
        revenue: {
          total: 0,
          pending: 0,
          monthly: 0,
          daily: 0
        }
      },
      transactions: [],
      selectedPeriod: 'monthly',
      loading: false,
      error: null,
      selectedMonth: currentMonth,
      selectedYear: currentYear,
      startDate: firstDayOfMonth.toISOString().split('T')[0],
      endDate: lastDayOfMonth.toISOString().split('T')[0],
      months: [
        { value: 1, label: 'January' },
        { value: 2, label: 'February' },
        { value: 3, label: 'March' },
        { value: 4, label: 'April' },
        { value: 5, label: 'May' },
        { value: 6, label: 'June' },
        { value: 7, label: 'July' },
        { value: 8, label: 'August' },
        { value: 9, label: 'September' },
        { value: 10, label: 'October' },
        { value: 11, label: 'November' },
        { value: 12, label: 'December' }
      ],
      years: Array.from({ length: 5 }, (_, i) => currentYear - i),
      boardingHouses: [],
      selectedHouse: 'all',
    };
  },
  computed: {
    filteredTransactions() {
      if (!this.transactions) return [];
      
      return this.transactions.filter(transaction => {
        const houseId = transaction.booking?.room?.boarding_house?.id;
        console.log('Filtering transaction:', {
          transaction_id: transaction.payment_id,
          house_id: houseId,
          selected_house: this.selectedHouse,
          amount: transaction.amount
        });
        
        // Filter by boarding house
        if (this.selectedHouse !== 'all' && 
            houseId !== parseInt(this.selectedHouse)) {
          return false;
        }
        
        const date = new Date(transaction.created_at);
        return this.isDateInRange(date);
      });
    },

    totalRevenue() {
      const total = this.filteredTransactions.reduce((sum, t) => {
        const amount = parseFloat(t.amount || 0);
        console.log(`Computing total - Transaction:`, {
          amount,
          house: t.booking?.room?.boarding_house?.name,
          house_id: t.booking?.room?.boarding_house?.id,
          selected_house: this.selectedHouse
        });
        return sum + amount;
      }, 0);
      console.log('Total Revenue Computed:', total);
      return total;
    },

    totalBookings() {
      return this.filteredTransactions.length;
    },

    averagePerBooking() {
      if (!this.totalBookings) return 0;
      return this.totalRevenue / this.totalBookings;
    }
  },
  methods: {
    getProfilePicture(profilePicture) {
      try {
        if (!profilePicture) {
          return this.defaultProfilePic;
        }
        if (profilePicture instanceof File) {
          return URL.createObjectURL(profilePicture);
        }
        if (typeof profilePicture === 'string' && profilePicture.trim() !== '') {
          return profilePicture;
        }
        return this.defaultProfilePic;
      } catch (error) {
        console.error('Error getting profile picture:', error);
        return this.defaultProfilePic;
      }
    },

    handleImageError(e) {
      e.target.src = this.defaultProfilePic;
    },

    navigateMenu(event) {
      const route = event.target.value;
      if (route === '/admin/admin_login') {
        this.logout();
      } else {
        this.$router.push(route);
      }
    },

    async logout() {
      try {
        await axios.post('/admin/logout');
        localStorage.clear();
        this.$router.push('/admin/admin_login');
      } catch (error) {
        console.error('Logout error:', error);
        this.$router.push('/admin/admin_login');
      }
    },

    async fetchPayments() {
      this.loading = true;
      try {
        const response = await axios.get('/admin/fetch-payments');
        console.log('Payments response:', response.data);
        
        if (response.data.status === 'success') {
          this.transactions = response.data.data.payments
            .filter(payment => payment && payment.amount)
            .map(payment => ({
              ...payment,
              amount: parseFloat(payment.amount || 0),
              date: payment.created_at,
              house_name: payment.booking?.room?.boarding_house?.name || 'Unknown',
              room_name: payment.booking?.room?.room_name || 'Unknown',
              guest_name: payment.booking?.user?.name || 'Unknown',
              check_in_date: payment.booking?.check_in_date,
              check_out_date: payment.booking?.check_out_date,
              duration: payment.booking ? this.calculateDuration(
                payment.booking.check_in_date,
                payment.booking.check_out_date
              ) : 0
            }));
          
          console.log('Processed transactions:', this.transactions);
          console.log('Total amount:', this.transactions.reduce((sum, t) => sum + (t.amount || 0), 0));
          this.calculateRevenue();
        }
      } catch (error) {
        console.error('Error fetching payments:', error);
        this.error = 'Failed to fetch payments';
        this.transactions = [];
      } finally {
        this.loading = false;
      }
    },

    calculateDuration(checkIn, checkOut) {
      if (!checkIn || !checkOut) return 0;
      const start = new Date(checkIn);
      const end = new Date(checkOut);
      if (isNaN(start.getTime()) || isNaN(end.getTime())) return 0;
      const diffTime = Math.abs(end - start);
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    },

    calculateRevenue() {
      try {
        let filteredTransactions = [...this.transactions];
        
        if (this.selectedHouse !== 'all') {
          filteredTransactions = filteredTransactions.filter(t => {
            const matches = t.booking?.room?.boarding_house?.id === parseInt(this.selectedHouse);
            console.log('Filtering by house:', {
              transaction_id: t.payment_id,
              house_id: t.booking?.room?.boarding_house?.id,
              selected_house: this.selectedHouse,
              matches
            });
            return matches;
          });
        }

        const total = filteredTransactions.reduce((sum, t) => {
          const amount = parseFloat(t.amount || 0);
          console.log(`Adding amount: ${amount} from transaction:`, {
            payment_id: t.payment_id,
            house: t.booking?.room?.boarding_house?.name,
            amount
          });
          return sum + amount;
        }, 0);

        console.log('Calculated total revenue:', {
          total,
          filtered_count: filteredTransactions.length,
          selected_house: this.selectedHouse
        });
        
        this.revenueData.revenue.total = total;

        this.$nextTick(() => {
          this.updateChart();
        });

      } catch (error) {
        console.error('Error calculating revenue:', error);
      }
    },

    formatAmount(amount) {
      return `₱${parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })}`;
    },

    formatDate(date) {
      if (!date) return 'N/A';
      const parsedDate = new Date(date);
      if (isNaN(parsedDate.getTime())) return 'N/A';
      
      return parsedDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },

    formatNumber(value) {
      if (typeof value !== 'number') {
        value = parseFloat(value) || 0;
      }
      return value.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },

    isDateInRange(date) {
      if (!this.startDate || !this.endDate) return true;
      const checkDate = new Date(date);
      return checkDate >= new Date(this.startDate) && 
             checkDate <= new Date(this.endDate);
    },

    updateChart() {
      console.log('Updating chart...'); // Debug log
      
      if (this.chart) {
        this.chart.destroy();
      }

      const canvas = this.$refs.revenueChart;
      if (!canvas) {
        console.error('Canvas element not found');
        return;
      }

      const ctx = canvas.getContext('2d');
      const data = this.prepareChartData();
      
      console.log('Chart data:', data); // Debug log

      this.chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.labels,
          datasets: [{
            label: 'Revenue',
            data: data.values,
            borderColor: '#794646',
            backgroundColor: 'rgba(121, 70, 70, 0.1)',
            fill: true,
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: (context) => {
                  return `₱${context.parsed.y.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                  })}`;
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: (value) => {
                  return `₱${value.toLocaleString('en-US')}`;
                }
              }
            }
          }
        }
      });
    },

    prepareChartData() {
      if (!this.transactions || !this.transactions.length) {
        return {
          labels: [],
          values: []
        };
      }

      // Group transactions by date and sum amounts
      const groupedData = {};
      
      this.filteredTransactions.forEach(transaction => {
        const date = new Date(transaction.date || transaction.created_at);
        const dateKey = date.toISOString().split('T')[0];
        
        if (!groupedData[dateKey]) {
          groupedData[dateKey] = 0;
        }
        
        if (transaction.status === 'completed') {
          groupedData[dateKey] += parseFloat(transaction.amount || 0);
        }
      });

      // Sort dates
      const sortedDates = Object.keys(groupedData).sort();
      
      return {
        labels: sortedDates.map(date => {
          return new Date(date).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric'
          });
        }),
        values: sortedDates.map(date => groupedData[date])
      };
    },

    async fetchBoardingHouses() {
      try {
        console.log('Fetching boarding houses...');
        const response = await axios.get('/admin/fetch-boarding-houses');
        
        if (response.data.status === 'success') {
          this.boardingHouses = response.data.data.map(house => ({
            id: house.boarding_house_id,
            name: house.name
          }));
          console.log('Fetched boarding houses:', this.boardingHouses);
        }
      } catch (error) {
        console.error('Error fetching boarding houses:', error);
        this.boardingHouses = [];
      }
    },

    async fetchAdminProfile() {
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          throw new Error('No user ID found');
        }
        
        const response = await axios.get(`/admin/fetch-profile/${userId}`);
        if (response.data.status === 'success') {
          const userData = response.data.data;
          this.adminName = userData.name;
          this.adminProfile = {
            ...this.adminProfile,
            ...userData
          };
          
          // Update localStorage
          localStorage.setItem('userName', userData.name);
          if (userData.profile_picture) {
            localStorage.setItem('profilePicture', userData.profile_picture);
          }
        }
      } catch (error) {
        console.error('Error fetching admin profile:', error);
      }
    },
  },
  watch: {
    transactions: {
      handler() {
        this.$nextTick(() => {
          this.updateChart();
        });
      },
      deep: true
    },
    selectedPeriod() { this.updateChart(); },
    startDate() { this.updateChart(); },
    endDate() { this.updateChart(); },
    selectedHouse: {
      handler(newVal) {
        console.log('Selected house changed to:', newVal);
        this.calculateRevenue();
        this.$nextTick(() => {
          this.updateChart();
        });
      },
      immediate: true
    }
  },
  async mounted() {
    console.log('Component mounted');
    
    try {
      await this.fetchBoardingHouses(); // Fetch boarding houses first
      await this.fetchAdminProfile();
      await this.fetchPayments();
      
      this.$nextTick(() => {
        this.updateChart();
      });
    } catch (error) {
      console.error('Error initializing:', error);
    }
  }
};
</script>

<style scoped>
@import '@/assets/css/admin_panel.css';

.revenue-wrapper {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-top: 2rem;
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.filter-select {
  flex: 1;
  min-width: 200px;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #374151;
}

.date-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.date-input {
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #374151;
}

.date-separator {
  color: #6b7280;
}

.revenue-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #f9fafb;
  border-radius: 8px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  font-size: 2rem;
}

.stat-info h3 {
  color: #374151;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.amount {
  color: #794646;
  font-size: 1.5rem;
  font-weight: 600;
}

.revenue-chart {
  height: 400px;
  margin-bottom: 2rem;
  position: relative;
  width: 100%;
  background: white;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

canvas {
  width: 100% !important;
  height: 100% !important;
}

.revenue-table {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

th {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
}

tr:hover {
  background: #f9fafb;
}

@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }

  .filter-select {
    width: 100%;
  }

  .date-range {
    flex-direction: column;
    width: 100%;
  }

  .date-input {
    width: 100%;
  }

  .admin-profile-name {
    display: none;
  }
}

/* Add these profile-specific styles */
.profile-section {
  margin-left: auto !important;
  margin-right: 1rem !important;
  display: flex;
  align-items: center;
}

.admin-profile {
  display: flex;
  margin-right: 1rem;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border-radius: 8px;
  transition: background-color 0.2s;
}

.profile-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  border: none;
}

.admin-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

.menu-container {
  margin-left: 0 !important;
}

/* Update responsive styles */
@media (max-width: 768px) {
  .admin-profile-name {
    display: none;
  }

  .menu-container {
    margin-right: 1rem;
  }

  .navbars {
    padding: 0 0.5rem;
  }

  .logos {
    height: 32px;
  }

  .nl h5 {
    font-size: 1rem;
  }
}

.nl {
  text-decoration: none;
  position: relative;
}

.nl::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 2px;
  bottom: -4px;
  left: 0;
  background-color: #794646;
  transform: scaleX(0);
  transform-origin: bottom right;
  transition: transform 0.3s ease-out;
}

.nl:hover::after {
  transform: scaleX(1);
  transform-origin: bottom left;
}

.nl h5 {
  color: #794646;
  margin: 0;
  transform: translate(0%, 10%);
  font-size: 1.5rem;
  font-weight: 700;
}
</style> 
