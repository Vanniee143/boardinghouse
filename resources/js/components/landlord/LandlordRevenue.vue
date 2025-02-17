<template>
  <div class="landlord-panel">
    <div class="navbars">
      <router-link to="/landlord/dashboard" class="logo-link">
        <img src="@/assets/images/image.png" alt="logo" class="logos">
      </router-link>
      <router-link to="/landlord/dashboard" class="nl">
        <h5>SBH BOOKING</h5>
      </router-link>

      <div class="profile-section">
        <div class="landlord-profile">
          <img 
            :src="getProfilePicture(landlordProfile.profile_picture)"
            alt="profile" 
            class="profile-icon"
            @error="handleImageError"
            @load="handleImageLoad"
          />
          <span class="landlord-profile-name">{{ landlordName }}</span>
        </div>
      </div>

      <div class="menu-container">
        <img src="@/assets/images/Menu.png" alt="menu" class="menuimgg" id="menuImage">
        <select class="Menu" id="menuSelect" @change="navigateMenu">
          <option value="#" disabled selected hidden>Menu</option>
          <option value="/landlord/profile">Profile Settings</option>
          <option value="/landlord/login">Logout</option>
        </select>
      </div>
    </div>

    <div class="overall">
      <div class="home">
        <h4 class="helper">REVENUE</h4>
      </div>

      <div class="revenue-wrapper">
        <div class="filters">
          <select v-model="selectedHouse" class="filter-select">
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
  name: 'LandlordRevenue',
  data() {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth() + 1;
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    return {
      landlordName: localStorage.getItem('userName') || 'Landlord',
      landlordProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId')
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
          pending: 0
        }
      },
      transactions: [],
      selectedPeriod: 'all',
      loading: false,
      error: null,
      selectedMonth: currentMonth,
      selectedYear: currentYear,
      startDate: firstDayOfMonth.toISOString().split('T')[0],
      endDate: lastDayOfMonth.toISOString().split('T')[0],
      boardingHouses: [],
      selectedHouse: 'all',
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
      paymentStats: {
        completed: 0,
        pending: 0,
        cancelled: 0
      }
    };
  },
  computed: {
    filteredTransactions() {
      if (!this.transactions) return [];
      
      return this.transactions.filter(transaction => {
        if (transaction.status !== 'completed') return false;
        
        const date = new Date(transaction.date);
        const monthMatch = date.getMonth() + 1 === this.selectedMonth;
        const yearMatch = date.getFullYear() === this.selectedYear;
        const inDateRange = this.isDateInRange(date);
        return monthMatch && yearMatch && inDateRange;
      });
    },
    totalRevenue() {
      return this.revenueData?.revenue?.total || 0;
    },
    totalBookings() {
      return this.revenueData?.bookingStats?.total || 0;
    },
    averagePerBooking() {
      return this.totalBookings ? this.totalRevenue / this.totalBookings : 0;
    }
  },
  methods: {
    navigateMenu(event) {
      const route = event.target.value;
      if (route === '/landlord/login') {
        this.logout();
      } else {
        this.$router.push(route);
      }
    },
    async logout() {
      try {
        await axios.post('/api/landlord/logout');
        localStorage.removeItem('landlordToken');
        localStorage.removeItem('landlordName');
        localStorage.removeItem('landlordId');
        delete axios.defaults.headers.common['Authorization'];
        this.$router.push('/landlord/login');
      } catch (error) {
        console.error('Logout error:', error);
      }
    },
    formatAmount(amount) {
      return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
      }).format(amount);
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    formatNumber(number) {
      return number.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },
    isDateInRange(date) {
      if (!this.startDate || !this.endDate) return true;
      const checkDate = new Date(date);
      return checkDate >= new Date(this.startDate) && checkDate <= new Date(this.endDate);
    },
    async fetchTransactions() {
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          throw new Error('User ID not found');
        }

        this.loading = true;
        const response = await axios.get(`/landlord/revenue/${userId}`);
        
        if (response.data.status === 'success' && response.data.data) {
          if (!response.data.data.transactions) {
            throw new Error('No transaction data found');
          }

          this.transactions = response.data.data.transactions;
          this.revenueData = response.data.data;
          
          this.paymentStats = this.transactions.reduce((acc, transaction) => {
            const status = transaction.status.toLowerCase();
            if (acc[status] !== undefined) {
              acc[status]++;
            }
            return acc;
          }, {
            completed: 0,
            pending: 0,
            cancelled: 0
          });
        } else {
          throw new Error(response.data.message || 'Failed to fetch transactions');
        }
      } catch (error) {
        console.error('Error fetching transactions:', error);
        this.error = error.message || 'Failed to load transactions';
        this.transactions = [];
        this.revenueData = {
          bookingStats: { total: 0, confirmed: 0, pending: 0, cancelled: 0 },
          revenue: { total: 0, pending: 0 },
          transactions: []
        };
        this.paymentStats = {
          completed: 0,
          pending: 0,
          cancelled: 0
        };
      } finally {
        this.loading = false;
      }
    },
    updateChart() {
      if (this.chart) {
        this.chart.destroy();
      }

      const ctx = this.$refs.revenueChart.getContext('2d');
      const data = this.prepareChartData();

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

      const groupedData = this.transactions.reduce((acc, transaction) => {
        const date = new Date(transaction.date);
        const dateKey = date.toISOString().split('T')[0];
        
        if (!acc[dateKey]) {
          acc[dateKey] = 0;
        }
        
        if (transaction.status === 'completed') {
          acc[dateKey] += parseFloat(transaction.amount || 0);
        }
        
        return acc;
      }, {});

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
    getProfilePicture(profilePicture) {
      if (profilePicture && profilePicture.trim() !== '') {
        return profilePicture;
      }
      return this.defaultProfilePic;
    },

    handleImageError(e) {
      e.target.src = this.defaultProfilePic;
    },

    handleImageLoad() {
      console.log('Image loaded successfully');
    },

    async fetchLandlordProfile() {
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          throw new Error('No user ID found');
        }
        const response = await axios.get(`/landlord/fetch-profile/${userId}`);
        
        if (response.data.status === 'success') {
          const userData = response.data.data;
          this.landlordName = userData.name;
          this.landlordProfile.profile_picture = userData.profile_picture;
          
          localStorage.setItem('userName', userData.name);
          if (userData.profile_picture) {
            localStorage.setItem('profilePicture', userData.profile_picture);
          }
        }
      } catch (error) {
        console.error('Error fetching landlord profile:', error);
      }
    },

    async fetchBoardingHouses() {
      try {
        const userId = localStorage.getItem('userId');
        const response = await axios.get('/landlord/get-boarding-houses', {
          headers: {
            'X-User-Id': userId
          }
        });
        
        if (response.data.status === 'success') {
          this.boardingHouses = response.data.data || [];
        }
      } catch (error) {
        console.error('Error fetching boarding houses:', error);
        this.boardingHouses = [];
      }
    },

    updateDateRange() {
      const firstDay = new Date(this.selectedYear, this.selectedMonth - 1, 1);
      const lastDay = new Date(this.selectedYear, this.selectedMonth, 0);
      this.startDate = firstDay.toISOString().split('T')[0];
      this.endDate = lastDay.toISOString().split('T')[0];
    }
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
    selectedHouse() { this.updateChart(); },
    selectedPeriod() { this.updateChart(); },
    startDate() { this.updateChart(); },
    endDate() { this.updateChart(); },
    selectedMonth() {
      this.updateDateRange();
    },
    selectedYear() {
      this.updateDateRange();
    }
  },
  async created() {
    const userId = localStorage.getItem('userId');
    if (!userId) {
      this.$router.push('/landlord/login');
      return;
    }

    try {
      await Promise.all([
        this.fetchLandlordProfile(),
        this.fetchTransactions(),
        this.fetchBoardingHouses()
      ]);
    } catch (error) {
      console.error('Error in created hook:', error);
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.updateChart();
    });
  }
};
</script>

<style scoped>
@import '@/assets/css/landlord_panel.css';

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
}

.profile-section {
  margin-left: auto !important;  /* Force margin-left: auto */
  margin-right: 1rem !important; /* Reduced margin to accommodate menu */
  display: flex;
  align-items: center;
}

.landlord-profile {
  display: flex;
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
}

.landlord-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

.menu-container {
  margin-left: 0 !important;  /* Remove auto margin */
  margin-right: 2rem;  /* Add right margin */
}

@media (max-width: 768px) {
  .landlord-profile-name {
    display: none;
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