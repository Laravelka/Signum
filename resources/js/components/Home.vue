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
	<v-container class="pa-1 mb-10" v-else>
		<v-row dense class="mb-7" align="center">
			<v-col v-if="!monitors" cols="12" xs="12" class="pa-3">
				<v-alert
					border="right"
					colored-border
					type="error"
					elevation="2"
					align="center"
				>
					Пока пусто...
				</v-alert>
			</v-col>
			
			<div v-if="!loading" class="pa-2 d-flex justify-center" :style="`width: 100%;`">
				<v-btn
					text
					@click="updateMonitors"
				>Обновить</v-btn>
			</div>
			<v-col v-if="!loading" v-for="monitor in monitors" v-bind:key="monitor.mid" cols="12" xs="12" sm="6" md="4">
				<v-card>
					<v-img min-height="220" aspect-ratio="16/9" :src="`${monitor.screen}?hash=${monitor.hash}`">
							<v-row dense class="lightbox fill-height" align="end">
								<v-col>
									<v-btn :to="`/monitor/${monitor.mid}`" text dark>
										<v-fade-transition v-if="true">
											<v-avatar
												color="red"
												class="mb-1 v-avatar--metronome"
												size="12"
											></v-avatar>
										</v-fade-transition>
										<v-icon small color="red" left v-if="false">mdi-circle</v-icon>{{ monitor.name }}
									</v-btn>
								</v-col>
							</v-row>
					</v-img>
				</v-card>
			</v-col>
		</v-row>
	</v-container>
</template>

<style scoped>
	.lightbox {
		box-shadow: 0 0 20px inset rgba(0, 0, 0, 0.1);
		background-image: linear-gradient(to top, rgba(0, 0, 0, 0.6) 0%, transparent 75px);
	}
	
	@keyframes metronome-example {
		from {
			transform: scale(.5);
		}

		to {
			transform: scale(1);
		}
	}

	.v-avatar--metronome {
		top: 1.5px;
		margin-right: 8px; 
		animation-name: metronome-example;
		animation-iteration-count: infinite;
		animation-direction: alternate;
		animation-duration: 1s;
	}
</style>

<script>
	import {
		setCookie,
		getCookie
	} from '@/js/helpers/cookies';
	import { isJson } from '@/js/helpers/functions';
	
	export default {
		data: () => ({
			error: false,
			message: false,
			title: 'Главная',
			loading: true,
			monitors: false
		}),
		created() {
			this.getMonitors();
			this.$root.$emit('active-panel', {id: 0, title: this.title});
		},
		watch: {
			$route: 'getMonitors'
		},
		methods: {
			reloadPage() {
				location.reload();
			},
			updateMonitors() {
				sessionStorage.removeItem('getMonitors');
				this.loading = true;
				this.monitors = false;
				this.getMonitors();
			},
			getMonitors() {
				var app = this;
				var monitors = sessionStorage.getItem('getMonitors');

				if (monitors !== null) {
					app.monitors = JSON.parse(monitors);
					app.loading = false;
				} else {
					this.axios.get('/monitors/getAll')
					.then(response => {
						app.monitors = (response.data.length < 1 ? false : response.data);
						app.loading = false;
						
						if (app.monitors)
							sessionStorage.setItem('getMonitors', JSON.stringify(response.data));
					})
					.catch(error => {
						app.loading = false;
						app.error = true;
						
						console.log(error.response.data.message);
						
						if (isJson(error.response.data))
							app.message = JSON.parse(error.response.data).message ? JSON.parse(error.response.data).message : JSON.parse(error.response.data);
						else
							app.message = error.response.data;
						
						console.error(error);
					});
				}
			}
		}
	}
</script>