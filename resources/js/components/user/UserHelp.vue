<template>
  <UserLayout>
    <div class="help-container">
      <h2>Help & Support</h2>
      <form @submit.prevent="submitSupportRequest" class="support-form">
        <div class="form-group">
          <label>Subject</label>
          <select v-model="supportData.subject" required>
            <option value="">Select a subject</option>
            <option value="booking">Booking Issues</option>
            <option value="payment">Payment Issues</option>
            <option value="account">Account Issues</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea 
            v-model="supportData.description" 
            required
            rows="5"
            placeholder="Please describe your issue in detail"
          ></textarea>
        </div>

        <div class="form-group">
          <label>Priority</label>
          <select v-model="supportData.priority" required>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>
        </div>

        <button type="submit" class="submit-btn">Submit Request</button>
      </form>
    </div>
  </UserLayout>
</template>

<script>
import UserLayout from './layouts/UserLayout.vue';
import axios from 'axios';

export default {
  name: 'UserHelp',
  components: {
    UserLayout
  },
  data() {
    return {
      supportData: {
        subject: '',
        description: '',
        priority: 'low',
        user_id: localStorage.getItem('userId'),
        user_name: localStorage.getItem('userName'),
        status: 'pending'
      }
    }
  },
  methods: {
    async submitSupportRequest() {
      try {
        const response = await axios.post('/support/create', {
          subject: this.supportData.subject,
          description: this.supportData.description,
          priority: this.supportData.priority,
          user_id: localStorage.getItem('userId'),
          user_name: localStorage.getItem('userName'),
          status: 'pending'
        });

        if (response.data.status === 'success') {
          alert('Support request submitted successfully. You will receive email notifications about status updates.');
          this.resetForm();
        }
      } catch (error) {
        console.error('Error submitting support request:', error);
        alert('Failed to submit support request: ' + (error.response?.data?.message || error.message));
      }
    },
    resetForm() {
      this.supportData = {
        subject: '',
        description: '',
        priority: 'low',
        user_id: localStorage.getItem('userId'),
        user_name: localStorage.getItem('userName'),
        status: 'pending'
      };
    }
  }
}
</script>

<style scoped>
.help-container {
  transform: translate(15%, 13%);
  max-width: 800px;
  margin: 2rem auto;
  padding: 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

h2 {
  color: #794646;
  margin-bottom: 2rem;
}

.support-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  font-weight: 600;
  color: #374151;
}

select, textarea {
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 1rem;
}

textarea {
  resize: vertical;
}

.submit-btn {
  background: #794646;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.submit-btn:hover {
  background: #693c3c;
}
</style>