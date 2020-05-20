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
			<div class="d-flex w-100 align-center justify-center">
				<v-btn
					text
					@click="updateNotify"
				><v-icon left dark>mdi-refresh</v-icon> Обновить</v-btn>
			</div>
			<div class="d-flex w-100 mt-3 align-center justify-center">
				<v-alert v-if="message" :type="messageType" dark dismissible :style="{'min-width': '100%'}">
					{{ message }}
				</v-alert>
			</div>
			<v-dialog v-model="dialogOpen" persistent max-width="435px">
				<v-card>
					<v-card-title>
						<span class="headline">Отправить уведомление</span>
					</v-card-title>
					<v-card-text>
						<v-container>
							<v-row>
								<v-text-field
									label="Заголовок"
									v-model="form.message"
									:error="messages.message.state"
									:error-messages="messages.message.label"
									v-on:keyup.enter="createNotify"
								></v-text-field>
								<v-text-field
									label="Сообщение"
									v-model="form.body"
									:error="messages.body.state"
									:error-messages="messages.body.label"
									v-on:keyup.enter="createNotify"
								></v-text-field>
								<v-text-field
									label="Иконка"
									v-model="form.icon"
									:error="messages.icon.state"
									:error-messages="messages.icon.label"
									v-on:keyup.enter="createNotify"
								></v-text-field>
								<v-text-field
									label="Тип"
									v-model="form.type"
									:error="messages.type.state"
									:error-messages="messages.type.label"
									v-on:keyup.enter="createNotify"
								></v-text-field>
								<v-text-field
									label="Кому"
									v-model="form.with"
									:error="messages.with.state"
									:error-messages="messages.with.label"
									v-on:keyup.enter="createNotify"
								></v-text-field>
							</v-row>
						</v-container>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn text rounded @click="dialogOpen = false">Отмена</v-btn>
						<v-btn color="indigo" dark rounded @click="createNotify">Отправить</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
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
		<v-btn
			fab
			dark
			fixed
			bottom
			right
			color="primary"
			@click="dialogOpen = true"
		>
			<v-icon>mdi-plus</v-icon>
		</v-btn>
	</v-container>
</template>
<script>
	export default {
		data: () => ({
			error: false,
			loading: true,
			notifications: false,
			title: 'Уведомления',
			dialogOpen: false,
			messages: {
				message: {
					state: false,
					label: []
				},
				body: {
					state: false,
					label: []
				},
				icon: {
					state: false,
					label: []
				},
				type: {
					state: false,
					label: []
				},
				with: {
					state: false,
					label: []
				}
			},
			form: {
				message: null,
				body: null,
				icon: null,
				type: null,
				with: null,
			},
			message: null,
			messageType: 'error'
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
			},
			updateNotify() {
				this.loading = true;
				
				setTimeout(() => { 
					this.notifications = [
						{id: 1, icon: 'https://ex-coin.space/images/push-logo.png', body: 'Тестовый текст', message: 'Заголовок'},
						{id: 2, icon: 'https://ex-coin.space/images/push-money.png', body: 'Пора платить бабки', message: 'Неоплаченные услуги'}
					];
					this.loading = false;
				}, 1000);
			},
			createNotify() {
				
			},
			getNotify() {
				this.notifications = [
					{id: 1, icon: 'https://ex-coin.space/images/push-logo.png', body: 'Тестовый текст', message: 'Заголовок'},
					{id: 2, icon: 'https://ex-coin.space/images/push-money.png', body: 'Пора платить бабки', message: 'Неоплаченные услуги'}
				];
			}
		}
	}
</script>