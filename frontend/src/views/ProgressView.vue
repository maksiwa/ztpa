<template>
  <div class="progress-page">
    <div class="container">
      <header class="page-header">
        <h1>📊 Twoje postępy</h1>
        <p>Śledź swój rozwój i osiągnięcia</p>
      </header>

      <div v-if="loading" class="loading">Ładowanie...</div>
      
      <template v-else>
        <div class="stats-grid">
          <div class="card stat-card">
            <span class="stat-icon">🏆</span>
            <span class="stat-value">{{ progress?.totalPoints }}</span>
            <span class="stat-label">Punkty</span>
          </div>
          <div class="card stat-card">
            <span class="stat-icon">🎯</span>
            <span class="stat-value">{{ progress?.activeChallenges }}</span>
            <span class="stat-label">Aktywne</span>
          </div>
          <div class="card stat-card">
            <span class="stat-icon">✅</span>
            <span class="stat-value">{{ progress?.completedChallenges }}</span>
            <span class="stat-label">Ukończone</span>
          </div>
          <div class="card stat-card">
            <span class="stat-icon">🏅</span>
            <span class="stat-value">{{ progress?.achievements }}</span>
            <span class="stat-label">Odznaki</span>
          </div>
        </div>

        <section class="active-challenges">
          <h2>Aktywne wyzwania</h2>
          <div v-if="progress?.challenges.length === 0" class="empty-state">
            <p>Nie masz aktywnych wyzwań.</p>
            <router-link to="/challenges" class="btn btn-primary">Znajdź wyzwanie</router-link>
          </div>
          
          <div v-else class="challenges-list">
            <div v-for="c in progress?.challenges" :key="c.id" class="card challenge-item">
              <div class="challenge-info">
                <h3>{{ c.title }}</h3>
                <span class="remaining">{{ c.remainingDays }} dni pozostało</span>
              </div>
              <div class="challenge-progress">
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: c.progress + '%' }"></div>
                </div>
                <span>{{ c.progress }}%</span>
              </div>
            </div>
          </div>
        </section>

        <section class="quote-section">
          <div class="card quote-card">
            <button @click="fetchQuote" class="refresh-btn">🔄</button>
            <p class="quote-text">"{{ quote?.content }}"</p>
            <p class="quote-author">— {{ quote?.author }}</p>
          </div>
        </section>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../api'

interface Progress {
  totalPoints: number
  activeChallenges: number
  completedChallenges: number
  achievements: number
  challenges: { id: number; title: string; status: string; progress: number; remainingDays: number }[]
}

interface Quote {
  content: string
  author: string
}

const progress = ref<Progress | null>(null)
const quote = ref<Quote | null>(null)
const loading = ref(true)

async function fetchProgress() {
  loading.value = true
  try {
    const response = await api.get('/progress')
    progress.value = response.data
  } finally {
    loading.value = false
  }
}

async function fetchQuote() {
  const response = await api.get('/quotes/random')
  quote.value = response.data
}

onMounted(() => {
  fetchProgress()
  fetchQuote()
})
</script>

<style scoped>
.progress-page { padding: 2rem 0; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { margin-bottom: 0.5rem; }
.page-header p { color: #718096; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.5rem;
}

.stat-icon { font-size: 2rem; margin-bottom: 0.5rem; }
.stat-value { font-size: 2rem; font-weight: 700; color: var(--primary); }
.stat-label { color: #718096; font-size: 0.875rem; }

.active-challenges h2 { margin-bottom: 1rem; }

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #718096;
}

.empty-state .btn { margin-top: 1rem; }

.challenges-list { display: flex; flex-direction: column; gap: 1rem; }

.challenge-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.challenge-info h3 { font-size: 1rem; margin-bottom: 0.25rem; }
.remaining { font-size: 0.875rem; color: #718096; }

.challenge-progress {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-width: 150px;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: var(--primary);
  border-radius: 4px;
}

.quote-section { margin-top: 2rem; }

.quote-card {
  position: relative;
  text-align: center;
  background: linear-gradient(135deg, #667eea08 0%, #764ba208 100%);
}

.refresh-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
}

.quote-text { font-size: 1.25rem; font-style: italic; margin-bottom: 1rem; }
.quote-author { color: #718096; }
</style>
