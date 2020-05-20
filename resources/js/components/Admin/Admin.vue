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
					@click="updateAdmins"
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
						<span class="headline">Добавить админа</span>
					</v-card-title>
					<v-card-text>
						<v-container>
							<v-row>
								<v-text-field
									label="Имя"
									v-model="name"
									:error="messages.name.state"
									:error-messages="messages.name.label"
									v-on:keyup.enter="createAdmin"
								></v-text-field>
								<v-text-field
									type="email"
									label="E-mail"
									v-model="email"
									:error="messages.email.state"
									:error-messages="messages.email.label"
									v-on:keyup.enter="createAdmin"
								></v-text-field>
								<v-text-field
									label="Пароль"
									v-model="password"
									:error="messages.password.state"
									:error-messages="messages.password.label"
									v-on:keyup.enter="createAdmin"
								></v-text-field>
							</v-row>
						</v-container>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn text rounded @click="dialogOpen = false">Отмена</v-btn>
						<v-btn color="indigo" dark rounded @click="createAdmin">Добавить</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
			<v-col width="100%" v-if="admins">
				<v-card tile>
					<v-list rounded two-line>
						<v-subheader>{{ title }}</v-subheader>
						<v-list-item-group v-model="admins">
							<v-list-item
								v-for="(admin, i) in admins"
								:key="i"
							>
								<v-list-item-content>
									<v-list-item-title class="d-flex justify-space-between" @click="return;">{{ admin.name }} <v-btn icon @click="deleteAdmin(admin.id)" :style="{top: '-6px', right: '-6px'}"><v-icon>mdi-delete</v-icon></v-btn></v-list-item-title>
									<v-list-item-subtitle v-text="admin.email"></v-list-item-subtitle>
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
        props: {
            source: String,
        },
		data: () => ({
			error: false,
			loading: true,
			title: 'Администраторы',
			admins: null,
			isEmpty: false,
			dialogOpen: false,
			name: null,
			email: null,
			password: null,
			message: null,
			messageType: 'error',
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
				}
			},
		}),
		created() {
			console.log('Admins is created');
			
			this.getAdmins();
			this.$root.$emit('active-panel', {
				id: -1,
				title: this.title
			});
		},
		watch: {
			admins(newAdmins, oldAdmins) {
				if (newAdmins)
					this.name = 'Admin #' + (newAdmins.length + 1);
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
			reloadPage() {
				location.reload();
			},
			createAdmin() {
				let app = this;
				
				const data = {
					name: this.name,
					email: this.email,
					password: this.password,
				};
				
				this.axios.post('admin/admins/create', data)
				.then(response => {
					const data = response.data;
					
					this.admins.push(data.user);
					sessionStorage.setItem('getAdmins', JSON.stringify(this.admins));
					
					app.dialogOpen = false;
					app.alert(data.message, 'success');
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
			deleteAdmin(id) {
				let app = this;
				
				this.axios.post('admin/admins/delete', {id: id})
				.then(response => {
					const data = response.data;
					
					arrUnset(this.users, id);
					sessionStorage.setItem('getAdmins', JSON.stringify(this.users));
					
					this.alert(data.message, 'success');
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
			updateAdmins() {
				this.loading = true;
				sessionStorage.removeItem('getAdmins');
				this.admins = null;
				this.getAdmins();
			},
			getAdmins() {
				var app = this;
				var admins = sessionStorage.getItem('getAdmins');

				if (admins !== null) {
					app.admins = JSON.parse(admins);
					app.loading = false;
				} else {
					this.axios.get('admin/admins/getAll')
					.then(response => {
						app.admins = (response.data.admins.length < 1 ? false : response.data.admins);
						
						if (app.admins)
						{
							sessionStorage.setItem('getAdmins', JSON.stringify(response.data.admins));
						}
						app.loading = false;
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
		
	}
</script>
