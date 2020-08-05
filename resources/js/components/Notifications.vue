<template>
	<v-container
		class="text-center"
		fill-height
		style="height: calc(100vh - 100px);" 
		v-if="loading"
	>
		<v-row align="center">
			<v-col class="loading" align="center">
				<v-progress-circular
					:size="50"
					:width="4"
					color="primary"
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
		<v-row dense class="mb-7" align="center">
			<v-col width="100%" v-if="notifications">
				<v-card tile>
					<v-list rounded two-line>
						<v-subheader>{{ title }}</v-subheader>
						<v-list-item-group v-model="notifications">
							<v-list-item
								v-for="(notify, i) in notifications"
								:key="i"
							>
								<v-list-item-avatar>
									<v-img :src="notify.icon"></v-img>
								</v-list-item-avatar>
								<v-list-item-content>
									<v-list-item-title class="d-flex justify-space-between" @click="return;">{{ notify.message }} <v-btn icon @click="deleteNotify(notify.id)" :style="{top: '-6px'}"><v-icon>mdi-close</v-icon></v-btn></v-list-item-title>
									<v-list-item-subtitle v-text="notify.body"></v-list-item-subtitle>
								</v-list-item-content>
							</v-list-item>
						</v-list-item-group>
					</v-list>
				</v-card>
			</v-col>
		</v-row>
	</v-container>
</template>
<script>
	export default {
		data: () => ({
			error: false,
			loading: true,
			notifications: false,
			title: 'Уведомления'
		}),
		created() {
			this.$root.$emit('active-panel', {id: 1, title: this.title});
		},
		mounted() {
			var app = this;
			
			setTimeout(() => { 
				
				app.notifications = [
					{id: 1, icon: 'https://ex-coin.space/images/push-logo.png', body: 'Тестовый текст', message: 'Заголовок'},
					{id: 2, icon: 'https://ex-coin.space/images/push-money.png', body: 'Пора платить бабки', message: 'Неоплаченные услуги'}
				];
				app.loading = false;
			}, 1000);
		},
		methods: {
			deleteNotify(id) {
				console.log(id);
				
				return;
			}
		}
	}
</script>