<script setup lang="ts">
const router = useRouter()

async function redirect() {
  await router.replace({ name: 'clients' })
}

nextTick(redirect)
</script>
