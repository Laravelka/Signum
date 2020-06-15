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
	<v-container class="pa-3" v-else-if="!loading">
		<v-row dense class="mb-7" align="center">
			<div class="d-flex w-100 mt-3 align-center justify-center">
				<v-alert v-if="message" :type="messageType" dark dismissible :style="{'min-width': '100%'}">
					{{ message }}
				</v-alert>
			</div>
			<v-dialog v-model="dialogOpen" persistent max-width="435px">
				<v-card>
					<v-card-title>
						<span class="headline">Добавить сервер</span>
					</v-card-title>
					<v-card-text>
						<v-container>
							<v-row>
								<v-text-field
									label="IP адрес"
									v-model="ip"
									:error="messages.ip.state"
									:error-messages="messages.ip.label"
									v-on:keyup.enter="createServer"
								></v-text-field>
								<v-text-field
									label="Порт"
									v-model="port"
									:error="messages.port.state"
									:error-messages="messages.port.label"
									v-on:keyup.enter="createServer"
								></v-text-field>
								<v-text-field
									label="Имя сервера"
									v-model="name"
									:error="messages.name.state"
									:error-messages="messages.name.label"
									v-on:keyup.enter="createServer"
								></v-text-field>
								<v-text-field
									type="email"
									label="E-mail администратора"
									v-model="super_email"
									:error="messages.super_email.state"
									:error-messages="messages.super_email.label"
									v-on:keyup.enter="createServer"
								></v-text-field>
								<v-text-field
									label="Пароль администратора"
									v-model="super_password"
									:error="messages.super_password.state"
									:error-messages="messages.super_password.label"
									v-on:keyup.enter="createServer"
								></v-text-field>
							</v-row>
						</v-container>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn text rounded @click="dialogOpen = false">Отмена</v-btn>
						<v-btn color="indigo" dark rounded @click="createServer">Добавить</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
			<v-col width="100%" v-if="servers">
				<v-card tile>
					<v-list two-line>
						<v-subheader>{{ title }}</v-subheader>
						<v-list-item-group v-model="servers">
							<v-list-item
								v-for="(server, i) in servers"
								:key="i"
								to="/admin/servers"
							>
								<v-list-item-content>
									<v-list-item-title v-text="server.name"></v-list-item-title>
									<v-list-item-subtitle v-text="'http://' + server.ip"></v-list-item-subtitle>
								</v-list-item-content>
								<v-list-item-icon @click="log(server, i)">
									<v-icon>mdi-close</v-icon>
								</v-list-item-icon>
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
        props: {
            source: String,
        },
		data: () => ({
			error: false,
			loading: true,
			title: 'Сервера',
			servers: null,
			message: null,
			isEmpty: false,
			dialogOpen: false,
			ip: null,
			port: 8080,
			name: null,
			super_email: null,
			super_password: null,
			messageType: 'error',
			messages: {
				ip: {
					state: false,
					label: []
				},
				port: {
					state: false,
					label: []
				},
				name: {
					state: false,
					label: []
				},
				super_email: {
					state: false,
					label: []
				},
				super_password: {
					state: false,
					label: []
				}
			},
		}),
		created() {
			var app = this;
			this.getServers();
			this.$root.$emit('active-panel', {
				id: -1,
				title: this.title
			});
			this.$root.$on('clickedRefreshButton', function(data) {
				app.updateServers();
			});
		},
		watch: {
			servers(newServers, oldServers) {
				if (newServers !== null)
					this.name = 'Server #' + (newServers.length + 1);
			}
		},
		methods: {
			log(args) {
				console.log(args);
			},
			reloadPage() {
				location.reload();
			},
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
			createServer() {
				let app = this;
				
				const data = {
					ip: this.ip,
					port: this.port,
					name: this.name,
					super_email: this.super_email,
					super_password: this.super_password,
				};
				
				this.axios.post('admin/servers/create', data)
				.then(response => {
					app.message = response.data.message;
					app.dialogOpen = false;
					app.messageType = 'success';
					
					app.servers.push(response.data.server);
					sessionStorage.setItem('getServers', JSON.stringify(app.servers));
				})
				.catch(error => {
					const data = error.response.data;
					
					if (data.messages)
					{
						for( let index in data.messages)
						{
							app.messages[index].state = true;
							app.messages[index].label = data.messages[index][0];
						}
						setTimeout(function() {
							app.messages = {
								ip: {
									state: false,
									label: []
								},
								port: {
									state: false,
									label: []
								},
								name: {
									state: false,
									label: []
								},
								super_email: {
									state: false,
									label: []
								},
								super_password: {
									state: false,
									label: []
								}
							};
						}, 5000);
					}
					else if (data.message)
					{
						app.message = data.message;
						
					}
					console.error(error);
				});
			},
			updateServers() {
				this.loading = true;
				sessionStorage.removeItem('getServers');
				this.servers = null;
				this.getServers();
			},
			getServers() {
				var app = this;
				var servers = sessionStorage.getItem('getServers');

				if (servers !== null) {
					app.servers = JSON.parse(servers);
					app.loading = false;
				} else {
					this.axios.get('admin/servers/getAll')
					.then(response => {
						app.servers = (response.data.servers.length < 1 ? false : response.data.servers);
						
						if (!app.servers) app.isEmpty = true;

						if (app.servers)
						{
							sessionStorage.setItem('getServers', JSON.stringify(response.data.servers));
						}
						app.loading = false;
					})
					.catch(error => {
						app.loading = false;
						app.error = true;
						
						if (isJson(error.response.data))
							app.message = JSON.parse(error.response.data).message ? JSON.parse(error.response.data).message : JSON.parse(error.response.data);
						else
							app.message = error.response.data;

						console.error(error);
					});
				}
				
				if (app.servers)
					app.name = 'Server #' + (app.servers.length + 1);
			},
			deleteUser(id) {
				let app = this;
				
				app.axios.delete('admin/servers/delete/' + id)
				.then(response => {
					const data = response.data;
					
					arrUnset(app.users, id);
					sessionStorage.setItem('getServers', JSON.stringify(app.users));
					
					this.alert(data.message, 'success');
				})
				.catch(error => {
					let data = false;

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
			},
		}
		
	}
</script>
