<script setup lang="ts">
const { status } = defineProps<{
  status: LessonStatus
}>()
</script>

<template>
	<div :class="`lesson-status lesson-status--${status}`" />
</template>

<style lang="scss">
.lesson-status {
	--size: 7px;
	--bg: red;
	height: var(--size);
	width: var(--size);
	border-radius: 50%;
	background-color: var(--bg);
	// top: -1px;
	// position: relative;
	&--planned {
		--bg: #949db1;
	}
	&--conducted {
		--bg: #00A272;
	}
	&--cancelled {
		--bg: #eb4432;
	}
}
</style>