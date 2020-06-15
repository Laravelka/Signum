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
						<span class="headline">Добавить пользователя</span>
					</v-card-title>
					<v-card-text>
						<v-container>
							<v-row>
								<v-text-field
									label="E-mail"
									v-model="form.email"
									:error="messages.email.state"
									:error-messages="messages.email.label"
									v-on:keyup.enter="createUser"
								></v-text-field>
								<v-text-field
									label="Имя"
									v-model="form.name"
									:error="messages.name.state"
									:error-messages="messages.name.label"
									v-on:keyup.enter="createUser"
								></v-text-field>
								<v-select
									:items="servers"
									label="Сервер"
									v-model="form.server_id"
									:error="messages.server_id.state"
									:error-messages="messages.server_id.label"
								></v-select>
								<v-text-field
									label="Пароль"
									v-model="form.password"
									:error="messages.password.state"
									:error-messages="messages.password.label"
									v-on:keyup.enter="createUser"
								></v-text-field>
								<v-text-field
									label="Ключ shinobi"
									v-model="form.shinobi_ke"
									:error="messages.shinobi_ke.state"
									:error-messages="messages.shinobi_ke.label"
									v-on:keyup.enter="createUser"
								></v-text-field>
								<v-text-field
									label="Пароль shinobi"
									v-model="form.shinobi_password"
									:error="messages.shinobi_password.state"
									:error-messages="messages.shinobi_password.label"
									v-on:keyup.enter="createUser"
								></v-text-field>
							</v-row>
						</v-container>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn text rounded @click="dialogOpen = false">Отмена</v-btn>
						<v-btn color="indigo" dark rounded @click="createUser">Отправить</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
			<v-col width="100%" v-if="users">
				<v-card tile>
					<v-list two-line>
						<v-subheader>{{ title }}</v-subheader>
						<v-list-item-group v-model="users">
							<v-list-item
								v-for="(user, i) in users"
								:key="i"
								to="/admin/users"
							>
								<v-list-item-content>
									<v-list-item-title class="d-flex justify-space-between">
										{{ user.name }} <v-btn icon @click="deleteUser(user.id)" :style="{top: '-6px', right: '-6px'}"><v-icon>mdi-close</v-icon></v-btn>
									</v-list-item-title>
									<v-list-item-subtitle v-text="user.email"></v-list-item-subtitle>
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
<style>
	.w-100 {
		width: 100%!important;
	}
</style>
<script>
	import { isJson } from '@/js/helpers/functions';
	import { arrSearch, arrUnset } from '@/js/helpers/array';
	
	export default {
		data: () => ({
			error: false,
			loading: true,
			users: false,
			servers: false,
			isEmpty: false,
			title: 'Пользователи',
			dialogOpen: false,
			currentUser: false,
			dialogTwoOpen: false,
			messages: {
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
			},
			form: {
				email: null,
				name: null,
				password: null,
				server_id: null,
				shinobi_ke: null,
				shinobi_password: null,
			},
			message: null,
			messageType: 'error'
		}),
		watch: {
			async dialogOpen(newOpen, oldOpen) {
				if (newOpen == true)
				{
					const json = sessionStorage.getItem('getServers');
					if (json !== null) {
						let servers = JSON.parse(json);
						
						servers.forEach((item, key) => {
							this.servers[key] = {text: item.name + ' - ' + item.ip, value: item.id};
						});
					} else if (this.servers) {
						
						this.servers.forEach((item, key) => {
							this.servers[key] = {text: item.name + ' - ' + item.ip, value: item.id};
						});
					}
				}
			}
		},
		created() {
			var app = this;
			this.getUsers();
			this.getServers();
			this.$root.$emit('active-panel', {
				id: -1,
				title: this.title
			});
			this.$root.$on('clickedRefreshButton', function(data) {
				app.updateUsers();
			});
		},
		methods: {
			log(params) {
				console.log(params);
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
			reloadPage() {
				location.reload();
			},
			getUserProfile(user) {
				this.currentUser = user;
				this.dialogTwoOpen = true;
			},
			deleteUser(id) {
				let app = this;
				
				app.axios.delete('admin/users/delete/' + id)
				.then(response => {
					const data = response.data;
					
					arrUnset(app.users, id);
					sessionStorage.setItem('getUsers', JSON.stringify(app.users));
					
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
			createUser() {
				var app = this;
				
				app.axios.post('admin/users/create', this.form)
				.then(response => {
					const data = response.data;
					
					app.users.push(data.user);
					sessionStorage.setItem('getUsers', JSON.stringify(this.users));
					
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
			updateUsers() {
				this.loading = true;
				
				sessionStorage.removeItem('getUsers');
				sessionStorage.removeItem('getServers');
				
				this.users = null;
				this.servers = null;
				
				this.getUsers();
				this.getServers();
			},
			getUsers() {
				var app = this;
				var users = sessionStorage.getItem('getUsers');

				if (users !== null) {
					app.users = JSON.parse(users);
					app.loading = false;
				} else {
					this.axios.get('admin/users/getAll')
					.then(response => {
						app.users = (response.data.users.length < 1 ? false : response.data.users);
						
						if (app.users)
						{
							sessionStorage.setItem('getUsers', JSON.stringify(response.data.users));
						}
						app.loading = false;
						app.message = null;
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
				}
			},
			getServers() {
				this.axios.get('admin/servers/getAll')
				.then(response => {
					const data = response.data;
					
					this.loading = false;
					if (data.servers)
					{
						this.servers = data.servers;
						sessionStorage.setItem('getServers', JSON.stringify(data.servers));
					}
					else
					{
						this.dialogOpen = false;
						this.alert('Для начала добавьте сервера!');
					}
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
			}
		}
	}
</script>