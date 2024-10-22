<script setup lang="ts">
const router = useRouter()
const route = useRoute()

async function redirect() {
  const { data } = await useHttp<PersonResource>(`parents/${route.params.id}`)
  const { id } = data.value!
  await router.replace({
    name: 'clients-id',
    params: { id },
  })
}

nextTick(redirect)
</script>
