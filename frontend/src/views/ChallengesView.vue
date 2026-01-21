<template>
  <div class="challenges-page">
    <div class="container">
      <header class="page-header">
        <h1>🎯 Wyzwania</h1>
        <p>Wybierz wyzwanie i rozpocznij swój cyfrowy detoks</p>
      </header>

      <div v-if="loading" class="loading">Ładowanie...</div>
      
      <div v-else class="challenges-grid">
        <div v-for="challenge in challenges" :key="challenge.id" class="card challenge-card">
          <div class="challenge-header">
            <span :class="['badge', `badge-${challenge.difficultyLevel}`]">
              {{ difficultyLabels[challenge.difficultyLevel] }}
            </span>
            <span class="points">{{ challenge.points }} pkt</span>
          </div>
          
          <h3>{{ challenge.title }}</h3>
          <p>{{ challenge.description }}</p>
          
          <div class="challenge-meta">
            <span>⏱️ {{ challenge.durationDays }} dni</span>
            <span>👥 {{ challenge.participantsCount }} uczestników</span>
          </div>
          
          <div v-if="challenge.isJoined" class="progress-bar">
            <div class="progress-fill" :style="{ width: challenge.progress + '%' }"></div>
            <span>{{ challenge.progress }}%</span>
          </div>
          
          <button 
            v-if="!challenge.isJoined" 
            @click="joinChallenge(challenge.id)"
            class="btn btn-primary"
          >
            Dołącz
          </button>
          <button 
            v-else 
            @click="completeChallenge(challenge.id)"
            class="btn btn-secondary"
          >
            Ukończ wyzwanie
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../api'

interface Challenge {
  id: number
  title: string
  description: string
  durationDays: number
  difficultyLevel: 'easy' | 'medium' | 'hard'
  points: number
  participantsCount: number
  isJoined: boolean
  progress: number | null
}

const challenges = ref<Challenge[]>([])
const loading = ref(true)

const difficultyLabels = {
  easy: 'Łatwe',
  medium: 'Średnie',
  hard: 'Trudne'
}

async function fetchChallenges() {
  loading.value = true
  try {
    const response = await api.get('/challenges')
    challenges.value = response.data
  } finally {
    loading.value = false
  }
}

async function joinChallenge(id: number) {
  await api.post(`/challenges/${id}/join`)
  await fetchChallenges()
}

async function completeChallenge(id: number) {
  await api.post(`/challenges/${id}/complete`)
  await fetchChallenges()
}

onMounted(fetchChallenges)
</script>

<style scoped>
.challenges-page {
  padding: 2rem 0;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 { margin-bottom: 0.5rem; }
.page-header p { color: #718096; }

.challenges-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.challenge-card {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.challenge-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.points {
  font-weight: 600;
  color: var(--primary);
}

.challenge-card h3 { font-size: 1.25rem; }
.challenge-card p { color: #718096; flex-grow: 1; }

.challenge-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.875rem;
  color: #718096;
}

.progress-bar {
  height: 24px;
  background: #e2e8f0;
  border-radius: 12px;
  position: relative;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--primary), var(--primary-dark));
  border-radius: 12px;
  transition: width 0.3s;
}

.progress-bar span {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.75rem;
  font-weight: 600;
}

.challenge-card .btn { width: 100%; }
</style>
