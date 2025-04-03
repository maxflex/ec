<script setup lang="ts">
const router = useRouter()

async function redirect() {
  await router.replace({ name: 'contracts' })
}

nextTick(redirect)
</script>
