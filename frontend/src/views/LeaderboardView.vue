<template>
  <div class="leaderboard-page">
    <div class="container">
      <!-- Header z animowanym gradientem -->
      <header class="page-header">
        <div class="header-content">
          <h1>🏆 Ranking</h1>
          <p>Rywalizuj z innymi i zdobywaj punkty!</p>
        </div>
      </header>

      <!-- Streak Banner -->
      <section class="streak-banner" v-if="streakData">
        <div class="streak-card" :class="{ 'streak-active': streakData.streakActive }">
          <div class="streak-flame">
            <span class="flame-icon">🔥</span>
            <span class="streak-count">{{ streakData.currentStreak }}</span>
          </div>
          <div class="streak-info">
            <h3>Twoja seria</h3>
            <p v-if="streakData.streakActive">{{ streakData.currentStreak }} dni z rzędu!</p>
            <p v-else class="streak-warning">Seria przerwana! Zamelduj się, żeby zacząć od nowa.</p>
            <div class="streak-meta">
              <span>🏅 Rekord: {{ streakData.maxStreak }} dni</span>
            </div>
          </div>
          <button 
            v-if="streakData.needsCheckIn" 
            @click="checkIn" 
            class="btn btn-checkin"
            :disabled="checkingIn"
          >
            {{ checkingIn ? '...' : '✓ Zamelduj się' }}
          </button>
          <div v-else class="checked-in">
            ✅ Zameldowano dziś!
          </div>
        </div>
      </section>

      <!-- Moje statystyki -->
      <section class="my-stats" v-if="myStats">
        <div class="stats-grid">
          <div class="stat-item">
            <span class="stat-icon">📍</span>
            <span class="stat-value">#{{ myStats.rank }}</span>
            <span class="stat-label">Twoja pozycja</span>
          </div>
          <div class="stat-item">
            <span class="stat-icon">⭐</span>
            <span class="stat-value">{{ myStats.points }}</span>
            <span class="stat-label">Punkty</span>
          </div>
          <div class="stat-item">
            <span class="stat-icon">🔥</span>
            <span class="stat-value">{{ myStats.currentStreak }}</span>
            <span class="stat-label">Streak</span>
          </div>
          <div class="stat-item">
            <span class="stat-icon">✅</span>
            <span class="stat-value">{{ myStats.completedChallenges }}</span>
            <span class="stat-label">Ukończone</span>
          </div>
        </div>
      </section>

      <!-- Leaderboard -->
      <section class="leaderboard-section">
        <h2>🏅 TOP 10</h2>
        <div v-if="loading" class="loading">
          <div class="spinner"></div>
          Ładowanie rankingu...
        </div>
        
        <div v-else class="leaderboard-list">
          <div 
            v-for="user in leaderboard" 
            :key="user.id" 
            class="leaderboard-item"
            :class="{ 
              'current-user': user.isCurrentUser,
              'top-1': user.rank === 1,
              'top-2': user.rank === 2,
              'top-3': user.rank === 3
            }"
          >
            <div class="rank-badge">
              <span v-if="user.rank === 1">🥇</span>
              <span v-else-if="user.rank === 2">🥈</span>
              <span v-else-if="user.rank === 3">🥉</span>
              <span v-else class="rank-number">#{{ user.rank }}</span>
            </div>
            
            <div class="user-info">
              <span class="user-name">{{ user.name }}</span>
              <div class="user-badges">
                <span v-if="user.streak >= 7" class="badge badge-streak" title="7+ dni streak">🔥</span>
                <span v-if="user.completedChallenges >= 5" class="badge badge-master" title="5+ ukończonych">⭐</span>
              </div>
            </div>
            
            <div class="user-stats">
              <span class="stat-points">{{ user.points }} pkt</span>
              <span class="stat-streak" v-if="user.streak > 0">🔥{{ user.streak }}</span>
            </div>
          </div>
          
          <div v-if="leaderboard.length === 0" class="empty-state">
            <p>Brak użytkowników w rankingu.</p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../api'

interface LeaderboardUser {
  rank: number
  id: number
  name: string
  points: number
  streak: number
  maxStreak: number
  completedChallenges: number
  isCurrentUser: boolean
}

interface MyStats {
  rank: number
  points: number
  currentStreak: number
  maxStreak: number
  completedChallenges: number
}

interface StreakData {
  currentStreak: number
  maxStreak: number
  lastActivityDate: string | null
  streakActive: boolean
  needsCheckIn: boolean
}

const leaderboard = ref<LeaderboardUser[]>([])
const myStats = ref<MyStats | null>(null)
const streakData = ref<StreakData | null>(null)
const loading = ref(true)
const checkingIn = ref(false)

async function fetchLeaderboard() {
  loading.value = true
  try {
    const response = await api.get('/leaderboard')
    leaderboard.value = response.data.leaderboard
    myStats.value = response.data.myStats
  } finally {
    loading.value = false
  }
}

async function fetchStreak() {
  try {
    const response = await api.get('/leaderboard/streak')
    streakData.value = response.data
  } catch (e) {
    console.error('Failed to fetch streak', e)
  }
}

async function checkIn() {
  checkingIn.value = true
  try {
    const response = await api.post('/leaderboard/checkin')
    // Update streak data
    if (streakData.value) {
      streakData.value.currentStreak = response.data.currentStreak
      streakData.value.maxStreak = response.data.maxStreak
      streakData.value.needsCheckIn = false
      streakData.value.streakActive = true
    }
    // Refresh stats
    await fetchLeaderboard()
  } finally {
    checkingIn.value = false
  }
}

onMounted(() => {
  fetchLeaderboard()
  fetchStreak()
})
</script>

<style scoped>
.leaderboard-page {
  padding: 2rem 0;
  min-height: 100vh;
  background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
  padding: 2rem;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.page-header h1 {
  font-size: 2.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #a0aec0;
}

/* Streak Banner */
.streak-banner {
  margin-bottom: 2rem;
}

.streak-card {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, rgba(255, 107, 0, 0.1) 0%, rgba(255, 69, 0, 0.1) 100%);
  border-radius: 16px;
  border: 1px solid rgba(255, 107, 0, 0.2);
  transition: all 0.3s ease;
}

.streak-card.streak-active {
  border-color: rgba(255, 107, 0, 0.5);
  box-shadow: 0 0 30px rgba(255, 107, 0, 0.2);
}

.streak-flame {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.flame-icon {
  font-size: 3rem;
  animation: flicker 1.5s ease-in-out infinite;
}

@keyframes flicker {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.streak-count {
  font-size: 2.5rem;
  font-weight: 800;
  color: #ff6b00;
}

.streak-info {
  flex: 1;
}

.streak-info h3 {
  margin: 0;
  color: #fff;
  font-size: 1.1rem;
}

.streak-info p {
  margin: 0.25rem 0;
  color: #ffa500;
}

.streak-warning {
  color: #ff6b6b !important;
}

.streak-meta {
  font-size: 0.85rem;
  color: #a0aec0;
}

.btn-checkin {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #ff6b00 0%, #ff4500 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-checkin:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 0 20px rgba(255, 107, 0, 0.4);
}

.btn-checkin:disabled {
  opacity: 0.7;
  cursor: wait;
}

.checked-in {
  padding: 0.75rem 1.5rem;
  background: rgba(72, 187, 120, 0.2);
  border-radius: 12px;
  color: #48bb78;
  font-weight: 600;
}

/* My Stats */
.my-stats {
  margin-bottom: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.stat-item:hover {
  transform: translateY(-5px);
  border-color: rgba(102, 126, 234, 0.5);
}

.stat-icon {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #fff;
}

.stat-label {
  font-size: 0.85rem;
  color: #a0aec0;
}

/* Leaderboard */
.leaderboard-section h2 {
  margin-bottom: 1rem;
  color: #fff;
}

.leaderboard-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.leaderboard-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.leaderboard-item:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateX(5px);
}

.leaderboard-item.current-user {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
  border-color: rgba(102, 126, 234, 0.5);
}

.leaderboard-item.top-1 {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.15) 0%, rgba(255, 193, 7, 0.1) 100%);
  border-color: rgba(255, 215, 0, 0.4);
}

.leaderboard-item.top-2 {
  background: linear-gradient(135deg, rgba(192, 192, 192, 0.1) 0%, rgba(169, 169, 169, 0.05) 100%);
  border-color: rgba(192, 192, 192, 0.3);
}

.leaderboard-item.top-3 {
  background: linear-gradient(135deg, rgba(205, 127, 50, 0.1) 0%, rgba(184, 115, 51, 0.05) 100%);
  border-color: rgba(205, 127, 50, 0.3);
}

.rank-badge {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
}

.rank-number {
  font-size: 1.25rem;
  font-weight: 700;
  color: #a0aec0;
}

.user-info {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-name {
  font-weight: 600;
  color: #fff;
  font-size: 1.1rem;
}

.user-badges {
  display: flex;
  gap: 0.25rem;
}

.badge {
  font-size: 1rem;
}

.user-stats {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-points {
  font-weight: 700;
  color: #667eea;
  font-size: 1.1rem;
}

.stat-streak {
  color: #ff6b00;
  font-weight: 600;
}

/* Loading */
.loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 3rem;
  color: #a0aec0;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid rgba(102, 126, 234, 0.3);
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #a0aec0;
}

/* Responsive */
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .streak-card {
    flex-direction: column;
    text-align: center;
  }
  
  .streak-info {
    text-align: center;
  }
}
</style>
