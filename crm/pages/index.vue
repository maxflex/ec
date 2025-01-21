<script setup lang="ts">
const router = useRouter()
const { isAdmin, isClient } = useAuthStore()

const name = isAdmin ? 'requests' : isClient ? 'journal' : 'groups'

async function redirect() {
  await router.replace({ name })
}

nextTick(redirect)
</script>
