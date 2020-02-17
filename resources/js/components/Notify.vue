<template>
    <v-container
		class="text-center"
		fill-height
		style="height: calc(100vh - 100px);" 
		v-if="loading && false"
	>
		<v-row align="center">
			<v-col class="loading" align="center">
				<v-progress-circular
					:size="50"
					:width="4"
					indeterminate
				></v-progress-circular>
			</v-col>
		</v-row>
	</v-container>
    <v-container
		class="text-center"
		fill-height
		style="height: calc(100vh - 100px);" 
		v-else-if="error"
	>
		<v-row align="center">
			<v-col class="loading">
				<h2 class="red--text lighten-2">
					Упс, ошибка
				</h2>
				<p>
					Зайдите чуть позже или обновите страницу
				</p>
				<v-btn @click="reloadPage" dark>Обновить</v-btn>
			</v-col>
		</v-row>
	</v-container>
    <v-container class="pa-3" v-else>
		<div v-if="loading">
			<v-skeleton-loader
				class="mb-3"
				v-for="skeleton in skeletons"
				v-bind:key="skeleton"
				type="list-item-two-line"
			></v-skeleton-loader>
		</div>
		<v-row v-else>
			<v-col class="pa-6 justify-center"><span>Позже</span></v-col>
		</v-row>
	</v-container>
</template>
<script>
	export default {
		data: () => ({
			skeletons: [1, 2, 3, 4, 5, 6, 7],
			error: false,
			loading: true,
			title: 'Уведомления'
		}),
		created() {
			this.$root.$emit('active-panel', {id: 1, title: this.title});
		},
		mounted() {
			var app = this;
			
			setTimeout(() => { app.loading = false; }, 1000);
		}
	}
</script>