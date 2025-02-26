<script setup lang="ts">
const router = useRouter()

async function redirect() {
  await router.replace({ name: 'requests' })
}

nextTick(redirect)
</script>
