<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
      <p class="text-gray-600 mt-1">Manage your account settings</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile</h2>
      <div v-if="auth.user" class="space-y-4">
        <div class="flex items-center gap-4">
          <img
            v-if="auth.user.avatar"
            :src="auth.user.avatar"
            :alt="auth.user.name"
            class="w-16 h-16 rounded-full"
          />
          <div>
            <div class="text-lg font-medium text-gray-900">{{ auth.user.name }}</div>
            <div class="text-sm text-gray-500">{{ auth.user.email }}</div>
          </div>
        </div>
        <div class="pt-4 border-t border-gray-200">
          <div class="text-sm text-gray-500">Signed in via</div>
          <div class="text-gray-900 capitalize">{{ auth.user.provider }}</div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Account</h2>
      <div class="space-y-4">
        <router-link
          to="/billing"
          class="block w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
        >
          <div class="font-medium text-gray-900">Billing & Subscription</div>
          <div class="text-sm text-gray-500">Manage your plan and payment methods</div>
        </router-link>
        <button
          @click="handleLogout"
          class="block w-full text-left px-4 py-3 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition"
        >
          <div class="font-medium">Sign Out</div>
          <div class="text-sm text-red-500">Sign out of your account</div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

function handleLogout() {
  auth.logout()
}
</script>
