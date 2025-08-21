<template>
  <div class="update-password">
    <h2>Update Password</h2>
    <form @submit.prevent="updatePassword">
      <div>
        <label for="currentPassword">Current Password</label>
        <input
          type="password"
          id="currentPassword"
          v-model="currentPassword"
          required
        />
      </div>

      <div>
        <label for="newPassword">New Password</label>
        <input
          type="password"
          id="newPassword"
          v-model="newPassword"
          required
        />
      </div>

      <div>
        <label for="confirmPassword">Confirm Password</label>
        <input
          type="password"
          id="confirmPassword"
          v-model="confirmPassword"
          required
        />
      </div>

      <button type="submit">Update Password</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>
    <p v-if="success" class="success">{{ success }}</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "UpdatePassword",
  data() {
    return {
      currentPassword: "",
      newPassword: "",
      confirmPassword: "",
      error: null,
      success: null,
    };
  },
  methods: {
    async updatePassword() {
      this.error = null;
      this.success = null;

      if (this.newPassword !== this.confirmPassword) {
        this.error = "New password and confirm password do not match.";
        return;
      }

      try {
        const token = localStorage.getItem("token"); // user must be logged in
        if (!token) {
          this.error = "You are not logged in.";
          return;
        }

        await axios.post(
          "http://127.0.0.1:8000/api/update-password",
          {
            current_password: this.currentPassword,
            new_password: this.newPassword,
            new_password_confirmation: this.confirmPassword,
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        this.success = "Password updated successfully!";
        this.currentPassword = "";
        this.newPassword = "";
        this.confirmPassword = "";

        // Redirect to TaskList.vue
        setTimeout(() => {
          this.$router.push({ name: "TaskList" });
        }, 1000);
      } catch (err) {
        if (err.response && err.response.data.message) {
          this.error = err.response.data.message;
        } else {
          this.error = "Failed to update password.";
        }
      }
    },
  },
};
</script>

<style scoped>
.update-password {
  max-width: 400px;
  margin: auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 6px;
}
.error {
  color: red;
}
.success {
  color: green;
}
</style>