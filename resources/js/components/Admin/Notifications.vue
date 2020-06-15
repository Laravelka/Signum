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
								<v-select
									:items="types"
									label="Тип"
									v-model="form.type"
									:error="messages.type.state"
									:error-messages="messages.type.label"
								></v-select>
								<v-select
									v-if="isTopic"
									:items="topics"
									label="Кому"
									v-model="form.with"
									:error="messages.with.state"
									:error-messages="messages.with.label"
								></v-select>
								<v-text-field
									v-else
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
	import {
		setCookie,
		getCookie
	} from '@/js/helpers/cookies';
	import { isJson } from '@/js/helpers/functions';
	import { arrSearch, arrUnset } from '@/js/helpers/array';
	
	export default {
		data: () => ({
			error: false,
			loading: true,
			isTopic: false,
			notifications: false,
			title: 'Уведомления',
			dialogOpen: false,
			types: [
				{text: 'Топик', value: 'topic'},
				{text: 'id юзера', value: 'token'}
			],
			topics: [
				{text: 'Администраторам', value: 'adminNotify'},
				{text: 'Пользователям', value: 'userNotify'}
			],
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
			var app = this;
			this.$root.$emit('active-panel', {id: 1, title: this.title});
			
			this.$root.$on('clickedRefreshButton', function(data) {
				app.updateNotify();
			});
		},
		mounted() {
			var app = this;
			this.getNotify();
			this.$root.$emit('active-panel', {
				id: -1,
				title: this.title
			});
			this.$root.$on('clickedRefreshButton', function(data) {
				app.updateNotify();
			});
		},
		watch: {
			'form.type': function(newVal, oldVal) {
				console.log('type: ', newVal, oldVal)
				
				if (newVal == 'topic')
					this.isTopic = true;
				else
					this.isTopic = false;
			}
		},
		methods: {
			alert(message, type = 'error', time = 3000) {
				this.message = message;
				this.messageType = type;
				
				if (time != false)
				{
					setTimeout(() => {
						this.message = null;
					}, time)
				}
			},
			deleteNotify(id) {
				console.log(id);
				
				return;
			},
			updateNotify() {
				this.loading = true;
				
				sessionStorage.removeItem('getUsers');
				sessionStorage.removeItem('getServers');
				
				this.notifications = null;
				this.getNotify();
			},
			createNotify() {
				var app = this;
				
				app.axios.post('admin/notify/create', this.form)
				.then(response => {
					const data = response.data;
					
					app.notifications.push(data.notify);
					sessionStorage.setItem('getNotify', JSON.stringify(this.notifications));
					
					app.dialogOpen = false;
					app.alert(data.message, 'success');
				})
				.catch((error) => {
					let data = false;
					
					if (isJson(error.response))
					{
						data = JSON.parse(error.response);
					}
					else if (typeof error.response == 'object')
					{
						data = error.response.data;
					}
					
					if (data.messages)
					{
						for( let index in data.messages)
						{
							app.messages[index].state = true;
							app.messages[index].label = data.messages[index][0];
						}
						setTimeout(function() {
							app.messages = {
								name: {
									state: false,
									label: []
								},
								email: {
									state: false,
									label: []
								},
								password: {
									state: false,
									label: []
								},
								server_id: {
									state: false,
									label: []
								},
								shinobi_ke: {
									state: false,
									label: []
								},
								shinobi_password: {
									state: false,
									label: []
								}
							};
						}, 5000);
					}
					else if (data.message)
					{
						this.dialogOpen = false;
						this.alert(data.message);
					}
					else
					{
						this.dialogOpen = false;
						this.error = true;
					}
					console.error(error);
				});
			},
			getNotify() {
				var notify = sessionStorage.getItem('getNotify');

				if (notify !== null) {
					this.notifications = JSON.parse(notify);
					this.loading = false;
				} else {
					this.axios.get('admin/notify/getAll')
					.then(response => {
						this.notifications = (response.data.notify.length < 1 ? false : response.data.notify);
						
						if (this.notify)
						{
							sessionStorage.setItem('getNotify', JSON.stringify(response.data.notify));
						}
						this.loading = false;
					})
					.catch(error => {
						let data = false;
						this.loading = false;
						
						if (isJson(error.response))
						{
							data = JSON.parse(error.response);
						}
						else if (typeof error.response == 'object')
						{
							data = error.response.data;
						}

						if (data.message)
						{
							this.dialogOpen = false;
							this.alert(data.message, 'error', false);
						}
						else
						{
							this.error = true;
						}
						console.error(error);
					});
				}
			}
		}
	}
</script>