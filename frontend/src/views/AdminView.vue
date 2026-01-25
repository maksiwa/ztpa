<template>
  <div class="admin-page">
    <div class="container">
      <header class="page-header">
        <h1>👑 Panel Administratora</h1>
        <p>Zarządzaj użytkownikami i monitoruj aktywność</p>
      </header>

      <!-- Stats Cards -->
      <section class="stats-section">
        <div class="stat-card">
          <div class="stat-icon">👥</div>
          <div class="stat-content">
            <span class="stat-value">{{ stats.users?.total || 0 }}</span>
            <span class="stat-label">Użytkownicy</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-content">
            <span class="stat-value">{{ stats.users?.active || 0 }}</span>
            <span class="stat-label">Aktywni</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🎯</div>
          <div class="stat-content">
            <span class="stat-value">{{ stats.challenges?.total || 0 }}</span>
            <span class="stat-label">Wyzwania</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🚫</div>
          <div class="stat-content">
            <span class="stat-value">{{ stats.users?.blocked || 0 }}</span>
            <span class="stat-label">Zablokowani</span>
          </div>
        </div>
      </section>

      <!-- Tabs -->
      <div class="tabs">
        <button 
          :class="['tab', { active: activeTab === 'users' }]"
          @click="activeTab = 'users'"
        >
          👥 Użytkownicy
        </button>
        <button 
          :class="['tab', { active: activeTab === 'logs' }]"
          @click="activeTab = 'logs'"
        >
          📋 Logi aktywności
        </button>
      </div>

      <!-- Users Table -->
      <section v-if="activeTab === 'users'" class="card">
        <h2>Lista użytkowników</h2>
        <div v-if="loadingUsers" class="loading">Ładowanie...</div>
        <table v-else class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Email</th>
              <th>Imię i nazwisko</th>
              <th>Role</th>
              <th>Status</th>
              <th>Data rejestracji</th>
              <th>Akcje</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.id }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.firstName }} {{ user.lastName }}</td>
              <td>
                <span v-for="role in user.roles" :key="role" :class="['role-badge', role === 'ROLE_ADMIN' ? 'admin' : '']">
                  {{ role === 'ROLE_ADMIN' ? '👑 Admin' : '👤 User' }}
                </span>
              </td>
              <td>
                <span :class="['status-badge', user.isActive ? 'active' : 'blocked']">
                  {{ user.isActive ? '✅ Aktywny' : '🚫 Zablokowany' }}
                </span>
              </td>
              <td>{{ formatDate(user.createdAt) }}</td>
              <td>
                <button 
                  v-if="user.roles && !user.roles.includes('ROLE_ADMIN')"
                  @click="toggleUser(user.id)"
                  :class="['btn-action', user.isActive ? 'block' : 'unblock']"
                >
                  {{ user.isActive ? '🔒 Zablokuj' : '🔓 Odblokuj' }}
                </button>
                <span v-else class="no-action">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- Activity Logs -->
      <section v-if="activeTab === 'logs'" class="card">
        <h2>Ostatnie logi aktywności</h2>
        <div v-if="loadingLogs" class="loading">Ładowanie...</div>
        <table v-else class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Użytkownik</th>
              <th>Akcja</th>
              <th>Adres IP</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id">
              <td>{{ log.id }}</td>
              <td>{{ log.user || 'System' }}</td>
              <td>{{ log.action }}</td>
              <td>{{ log.ip || '-' }}</td>
              <td>{{ formatDateTime(log.createdAt) }}</td>
            </tr>
          </tbody>
        </table>
        <p v-if="logs.length === 0 && !loadingLogs" class="empty-message">
          Brak logów aktywności
        </p>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../api'

interface Stats {
  users?: {
    total: number
    active: number
    blocked: number
  }
  challenges?: {
    total: number
  }
}

interface User {
  id: number
  email: string
  firstName: string
  lastName: string
  roles: string[]
  isActive: boolean
  createdAt: string
}

interface ActivityLog {
  id: number
  user: string | null
  action: string
  ip: string | null
  createdAt: string
}

const activeTab = ref<'users' | 'logs'>('users')
const stats = ref<Stats>({})
const users = ref<User[]>([])
const logs = ref<ActivityLog[]>([])
const loadingUsers = ref(true)
const loadingLogs = ref(true)

function formatDate(dateStr: string): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('pl-PL')
}

function formatDateTime(dateStr: string): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleString('pl-PL')
}

async function fetchStats() {
  try {
    const response = await api.get('/admin/stats')
    stats.value = response.data
  } catch (e) {
    console.error('Błąd pobierania statystyk:', e)
  }
}

async function fetchUsers() {
  loadingUsers.value = true
  try {
    const response = await api.get('/admin/users')
    users.value = response.data
  } catch (e) {
    console.error('Błąd pobierania użytkowników:', e)
  } finally {
    loadingUsers.value = false
  }
}

async function fetchLogs() {
  loadingLogs.value = true
  try {
    const response = await api.get('/admin/logs')
    logs.value = response.data
  } catch (e) {
    console.error('Błąd pobierania logów:', e)
  } finally {
    loadingLogs.value = false
  }
}

async function toggleUser(userId: number) {
  try {
    await api.post(`/admin/users/${userId}/toggle`)
    await fetchUsers()
    await fetchStats()
  } catch (e) {
    console.error('Błąd zmiany statusu użytkownika:', e)
  }
}

onMounted(() => {
  fetchStats()
  fetchUsers()
  fetchLogs()
})
</script>

<style scoped>
.admin-page {
  padding: 2rem 0;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 { margin-bottom: 0.5rem; }
.page-header p { color: #718096; }

/* Stats Section */
.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}

.stat-icon {
  font-size: 2.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #2d3748;
}

.stat-label {
  font-size: 0.875rem;
  color: #718096;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.tab {
  padding: 0.75rem 1.5rem;
  border: none;
  background: #e2e8f0;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.tab:hover {
  background: #cbd5e0;
}

.tab.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

/* Admin Table */
.admin-table {
  width: 100%;
  border-collapse: collapse;
}

.admin-table th,
.admin-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.admin-table th {
  background: #f7fafc;
  font-weight: 600;
  color: #4a5568;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.admin-table tr:hover {
  background: #f7fafc;
}

/* Badges */
.role-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  background: #e2e8f0;
  color: #4a5568;
  margin-right: 0.25rem;
}

.role-badge.admin {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 500;
}

.status-badge.active {
  background: #c6f6d5;
  color: #276749;
}

.status-badge.blocked {
  background: #fed7d7;
  color: #c53030;
}

/* Action Buttons */
.btn-action {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action.block {
  background: #fed7d7;
  color: #c53030;
}

.btn-action.block:hover {
  background: #fc8181;
  color: white;
}

.btn-action.unblock {
  background: #c6f6d5;
  color: #276749;
}

.btn-action.unblock:hover {
  background: #68d391;
  color: white;
}

.no-action {
  color: #a0aec0;
}

/* Card */
.card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

.card h2 {
  margin-bottom: 1.5rem;
  font-size: 1.25rem;
  color: #2d3748;
}

/* Utils */
.loading {
  text-align: center;
  padding: 2rem;
  color: #718096;
}

.empty-message {
  text-align: center;
  padding: 2rem;
  color: #a0aec0;
}
</style>
