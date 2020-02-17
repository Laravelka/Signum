<template>
	<v-form ref="form" v-model="valid" lazy-validation>
		<v-container class="fill-height" align="center" justify="center">
			<v-row align="center" justify="center">
				<v-col
					cols="12"
					xs="10"
					md="6"
					lg="4"
					align="center"
				>
					<h1 class="mb-9">
						Signum.video
					</h1>
					<v-alert
						type="error"
						elevation="3"
						class="mb-4"
						v-if="error"
					>
						{{ message }}
					</v-alert>
					<v-alert
						type="success"
						color="teal"
						colored-border
						elevation="3"
						class="mb-7"
						v-if="success"
					>
						<v-row align="center">
							<v-col>
								Вы успешно зарегистрировались!
							</v-col>
							<v-col>
								<router-link :to="{name: 'login'}">
									<v-btn color="teal" dark>Войти</v-btn>
								</router-link>
							</v-col>
						</v-row>
					</v-alert>
					<v-text-field
						v-model="name"
						:rules="nameRules"
						label="Имя"
						required
						outlined
						:error="messages.name.state"
						:error-messages="messages.name.label"
					></v-text-field>
					<v-text-field
						v-model="email"
						:rules="emailRules"
						label="E-mail"
						required
						outlined
						:error="messages.email.state"
						:error-messages="messages.email.label"
					></v-text-field>
					<v-text-field
						v-model="password"
						:rules="passwordRules"
						:type="show1 ? 'text' : 'password'"
						label="Пароль"
						@click:append="show1 = !show1"
						:error="messages.password.state"
						:error-messages="messages.password.label"
						outlined
						required
					></v-text-field>
					<div justify="space-between">
						<router-link :to="{name: 'login'}">
							<v-btn text small color="teal">
								Вход
							</v-btn>
						</router-link>
						<v-btn
							color="teal"
							class="mt-3"
							@click="validate"
							dark
						>Регистрация</v-btn>
					</div>
				</v-col>
			</v-row>
		</v-container>
	</v-form>
</template>
<script>
	export default {
		data: () => ({
			valid: false,
			error: false,
			success: false,
			show1: false,
			message: null,
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
			name: '',
			nameRules: [
				v => !!v || 'Введите имя.',
			],
			password: '',
			passwordRules: [
				v => !!v || 'Введите пароль.',
			],
			email: '',
			emailRules: [
				v => !!v || 'Введите E-mail.',
				v => /.+@.+/.test(v) || 'Неккоректный E-mail.',
			],
		}),
		methods: {
			validate() {
				let app = this;
				
				if (this.$refs.form.validate())
				{
					this.$auth.register({
						body: this.data,
						params: {
							name: app.name,
							email: app.email,
							password: app.password
						},
						error: function(error) {
							const response = error.response;
							
							if (response.data.error && response.data.message)
							{
								app.error = true;
								app.message = response.data.message;
								
								setTimeout(() => { app.error = false }, 5000);
							}
							else if (response.data.error && response.data.messages)
							{
								const messages = response.data.messages;
								
								for( let index in messages)
								{
									app.messages[index].state = true;
									app.messages[index].label = messages[index][0];
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
						},
						success: function(response) {
							
							if (response.data.error && response.data.message)
							{
								app.error = true;
								app.message = response.data.message;
								
								setTimeout(() => { app.error = false }, 5000);
							}
							else if (response.data.error && response.data.messages)
							{
								const messages = response.data.messages;
								
								for( let index in messages)
								{
									app.messages[index].state = true;
									app.messages[index].label = messages[index][0];
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
							else if (response.data.message)
							{
								app.success = true;
								app.message = response.data.message;
							}
						},
						rememberMe: true,
						fetchUser: false,
						redirect: true
					});
				}
			}
		}
	}
</script>
