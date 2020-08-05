<template>

    <v-container fill-height
		style="height: calc(100vh - 100px);"
	>
        <v-row align="center" justify="center">
            <v-col
                cols="12"
                sm="6"
                md="4"
                lg="4"
                align="center"
            >
                <v-form ref="form" v-model="valid" lazy-validation>
					<h1 class="mb-10">
						Signum.video
					</h1>
					<v-alert
						elevation="3"
						class="mb-4"
						v-if="message"
						:type="messageType"
					>
						{{ message }}
					</v-alert>
					<v-text-field
						label="E-mail"
						v-model="email"
						:rules="emailRules"
						:error="messages.email.state"
						:error-messages="messages.email.label"
						solo
						rounded
						v-on:keyup.enter="validate"
						prepend-inner-icon="mdi-mail"
					></v-text-field>
					<v-text-field
						v-model="password"
						:rules="passwordRules"
						:append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
						:type="show1 ? 'text' : 'password'"
						:error="messages.password.state"
						:error-messages="messages.password.label"
						label="Пароль"
						@click:append="show1 = !show1"
						solo
						rounded
						v-on:keyup.enter="validate"
						prepend-inner-icon="mdi-lock"
					></v-text-field>
					<div class="pa-1" justify="spce-between">
						<v-btn
							color="indigo"
							@click="validate"
							dark
							rounded
							block
						>Войти</v-btn>
					</div>
                </v-form>
            </v-col>
        </v-row>
    </v-container>
</template>
<script>
	export default {
        props: {
            source: String,
        },
		data: () => ({
			valid: false,
			error: false,
			show1: false,
			message: null,
			messageType: 'error',
			messages: {
				email: {
					state: false,
					label: []
				},
				password: {
					state: false,
					label: []
				}
			},
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
				if (this.$refs.form.validate())
				{
					this.auth();
				}
			},
			auth() {
				let app = this;
				
				app.$auth.login({
					body: app.data,
					params: {
						email: app.email,
						password: app.password
					},
					error: function(error) {
						const data = error.response.data;

						if (data.error && data.message)
						{
							app.error = true;
							app.message = data.message;

							setTimeout(() => { app.error = false }, 5000);
						}
						else if (data.error && data.messages)
						{
							const messages = data.messages;

							for( let index in messages)
							{
								app.messages[index].state = true;
								app.messages[index].label = messages[index][0];
							}
							setTimeout(function() {
								app.messages = {
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
						const data = response.data;
						
						if (data.error && data.message)
						{
							app.error = true;
							app.message = data.message;

							setTimeout(() => { app.error = false }, 5000);
						}
						else if (data.error && data.messages)
						{
							const messages = data.messages;

							for( let index in messages)
							{
								app.messages[index].state = true;
								app.messages[index].label = messages[index][0];
							}
							setTimeout(function() {
								app.messages = {
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
							app.message = data.message;
							app.messageType = 'success';
							
							if (data.user)
							{
								localStorage.setItem('userData', JSON.stringify(data.user));
								
								if (data.user.roles == 'admin')
								{
									app.$router.push('/admin');
								}
								else
								{
									app.$router.push('/');
								}
							}
							setTimeout(function() {
								app.message = null;
								app.messageType = 'error';
							}, 2000);
						}
					},
					rememberMe: true,
					fetchUser: true,
					redirect: false
				});
			}
		}
	}
</script>
