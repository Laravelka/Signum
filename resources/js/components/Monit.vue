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
	<v-container v-else>
		<v-row justify="center">
			<v-col xs="12" lg="12" xl="10">
				<v-card raised elevation="5">
					<div
						id="stream"
						class="is_overlay"
					>
						<div class="priview" v-if="isPreload">
							<v-progress-circular indeterminate color="primary"></v-progress-circular>
						</div>
						<div
							v-if="vidError"
							class="errorBlock"
						>
							<v-row align="center">
								<v-col>
									<h2 class="red--text lighten-2" align="center">
										Упс, ошибка!
									</h2>
									<p align="center">
										{{ isStream ? 'Стрим не запущен или был прерван' : 'Видео с заданной датой уже удалено или не найдено.' }}
									</p>
								</v-col>
							</v-row>
						</div>
						<video
							v-else
							ref="vid"
							id="video"
							autoplay="true"
							muted="muted"
							style="display: none;"
							preload="none"
							:title="monitor.name"
							:src="videoSrc"
							v-on:loadeddata="loadedData"
							v-on:error="videoError"
							v-on:canplay="log('video can play')"
							v-on:play="log('video start play')"
						></video>
					</div>
					<v-card-title>
						<v-row justify="space-between">
							<v-col cols="6">
								<v-btn 
									small
									elevation="0"
									color="text--bold"
									@click="playStream"
									:disabled="isStream && isPreload"
								>LIVE</v-btn>
							</v-col>
							<v-col cols="6" class="d-flex justify-end">
								<v-btn icon :disabled="!isStream" @click="downloadImg(monitor.server + monitor.snapshot, timeNow)">
									<v-icon>mdi-camera</v-icon>
								</v-btn>
								<v-btn icon :disabled="isPreload || isStream">
									<v-icon>mdi-cloud-download</v-icon>
								</v-btn>
								<v-btn icon @click="fullScreen" :disabled="isPreload">
									<v-icon>mdi-arrow-expand-all</v-icon>
								</v-btn>
							</v-col>
						</v-row>
					</v-card-title>
					<v-card-actions class="flex-column justify-center">
						<v-row class="d-flex justify-center">
							<div
								class="d-flex"
								align="center"
								justify="center"
							>
								<v-btn
									text
									small
									elevation="0"
									ref="realTime"
									v-text="timeNow"
								>
								</v-btn>
							</div>
							<v-btn fab text small>
								<v-icon small @click="prevData">mdi-chevron-left</v-icon>
							</v-btn>
							<span class="ml-2 mr-2">{{ archive.dateString }}</span>
							<v-btn fab text small @click="nextData">
								<v-icon small>mdi-chevron-right</v-icon>
							</v-btn>
						</v-row>
						<v-row class="" style="width: 100%;">
							<timeline :params="timelineParams" @setNewTimelineCenter="onSetNewTimelineCenter()" ref="timeline"></timeline>
						</v-row>
					</v-card-actions> 
				</v-card>
			</v-col>
		</v-row>
	</v-container>
</template>
<style scoped>
	#stream {
		width: 100%;
		height: 200px;
		overflow: hidden;
	}
	
	@media (max-width: 600px) {
		#stream {
			height: 190px!important;
		}
	}
	
	@media (min-width: 600px) and (max-width: 960px) {
		#stream {
			height: 360px!important;
		}
	}
	
	@media (min-width: 960px) {
		#stream {
			height: 430px!important;
		}
	}
	
	.darkening {
		background: rgba(0, 0, 0, 0.3);
	}
	
	.is_overlay { 
		display: block; 
		width: 100%; 
		height: 100%; 
	}
	
	#stream {
		position: relative;
	}
	
	#stream > #video {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%; 
		height: 100%;
		object-fit: unset;
	}
	
	.priview {
		background: rgba(0, 0, 0, 0.3);
		z-index: 1;
		position: absolute;
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	
	.errorBlock {
		background: rgba(0, 0, 0, 0.3);
		position: absolute;
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
	}
</style>
<script>
	import { ucFirst, fullScreen, fullScreenCancel } from '@/js/helpers/functions';
	import moment from 'moment';
	import lodash from 'lodash';
	import download from 'downloadjs';
	
	moment.locale('ru');
	
	export default {
		data: () => ({
			timeNow: moment().format('LTS'),
			videoSrc: false,
			title: 'Монитор',
			error: false,
			vidError: false,
			loaded: false,
			isStream: true,
			isPreload: true,
			loading: true,
			monitor: null,
			archive: {
				data: false,
				dateNow: false,
				dateString: false,
			},
			timelineParams: {
				range: {
					min: moment().subtract(1, 'week'), // максимум на 1 неделю назад
					max: moment().add(1, 'week'),
				},
				downloadeditem: null, 
				// center: moment().format(moment.HTML5_FMT.DATETIME_LOCAL_SECONDS),
				// average: moment(),
				center: null,
				average: null,
				play: false
			}
		}),
		watch: {
			'archive.dateNow': function(newDate, oldDate) {
				if (oldDate !== false)
					this.getVideoByDate(newDate);
			}
		},
		created() {
			var app = this;
			var now = moment();
			
			app.archive.dateNow = now;
			app.archive.dateString = ucFirst(now.format('dddd DD-MM-YYYY'));
			
			app.$root.$emit('hide-object', ['bottomNavigation']);
			
			app.axios.post('/monitors/getById', {id: app.$route.params.id})
			.then(response => {
				app.monitor = response.data;
				app.title = app.monitor.name;
				app.videoSrc = app.monitor.server + app.monitor.streams[0];
				app.$root.$emit('active-panel', {id: -1, title: app.title});
				
				app.loading = false;
			})
			.catch(error => {
				app.error = true;
				app.loading = false;
				
				console.error('Ошибка получения камеры: ', error);
			});

			this.changeVideoDateThottled = _.throttle(this.changeVideoDate, 500); // Ограничиеваем вызов раз в 500 мс

		},
		mounted() {
			this.updateTime();
			// this.$refs.vid.oncanplay = function() {
			// 	console.log(Date() + ': video can play')
			// };
			// this.$refs.vid.play = function() {
			// 	console.log(Date() + ': video start play')
			// }

		},
		methods: {
			downloadImg(url, name) {
				var xhr = new XMLHttpRequest();
				xhr.open("GET", url, true);
				xhr.responseType = 'blob';
				xhr.onload = function(e) {
					download(xhr.response, name + ".jpg", "image/jpeg" );
				}
				xhr.send();
			},
			updateTime() {
				var app = this;
				
				setTimeout(() => {
					app.timeNow = moment().format('LTS');
					app.updateTime();
				}, 250);
			},
			loadedData() {
				this.loaded = true;
				this.isPreload = false;
				this.$refs.vid.style.display = 'block';
				this.$refs.vid.play();
			},
			videoError(event) {
				console.error(event);
				
				// this.$refs.vid.src = '';
				this.isPreload = false; 
				this.vidError = true;
			},
			playStream() {
				this.videoSrc = this.monitor.server + this.monitor.streams[0];
				this.isStream = true;
				this.isPreload = true;
				this.vidError = false;
			},
			nextData() {
				var newDate = moment(this.archive.dateNow).add(1, 'days');
				
				if (moment(moment()).isBefore(newDate)) return;
				
				this.isStream = false;
				this.archive.dateNow = newDate;
				this.archive.dateString = ucFirst(
					newDate.
					format('dddd DD-MM-YYYY')
				);
			},
			prevData() {
				var newDate = moment(this.archive.dateNow).subtract(1, 'days');
				
				this.isStream = false;
				this.archive.dateNow = newDate;
				this.archive.dateString = ucFirst(
					newDate.
					format('dddd DD-MM-YYYY')
				);
			},
			getVideoByDate(date) {
				var app = this;
				
				app.vidError = false;
				app.isStream = false;
				app.isPreload = true;
				// app.$refs.vid.src = '';
				
				app.axios.post('/videos/getByMonitorId', {monitor_id: app.monitor.mid, date: date})
				.then(response => {
					const videos = response.data.videos;
					
					if (response.data.total < 1)
					{
						app.vidError = true;
						app.isPreload = false;
					}
					else
					{
						app.archive.data = videos;
						app.videoSrc = response.data.server + videos[0].href;
					}
				})
				.catch(error => {
					app.error = true;
					console.error('Ошибка получение видео архива: ', error)
				});
			},
			fullScreen() {
				fullScreen(this.$refs.vid);
			},
			reloadPage() {
				location.reload();
			},
			onSetNewTimelineCenter: function () {
				//console.log(this.timelineParams.center);
				this.changeVideoDateThottled(this.timelineParams.center);
				// this.getVideoByDate(this.timelineParams.center);
			},
			moveTimelineTo: function (time) {
				this.$refs['timeline'].moveTimelineTo(time);
			},

			// changeVideoDateThottled: function() {},
			changeVideoDate: function(date) {

				var app = this;
				app.vidError = false;
				app.isStream = false;
				app.isPreload = true;
				
				//console.log(new Date);

				if (app.processingPostRequest) {
					app.changeVideoDateThottled(date);
				}

				app.processingPostRequest = true;

				app.axios.post('/videos/getByMonitorId', {monitor_id: app.monitor.mid, date: date})
				.then(response => {

					//console.log('Answer time: '  + (new Date));

					app.processingPostRequest = false;

					const videos = response.data.videos;
					
					if (response.data.total < 1) 
					{
						app.vidError = true;
						app.isPreload = false;
					} 
					else 
					{
						app.archive.data = videos;
						app.videoSrc = response.data.server + videos[0].href;

						app.isStream = true;
						app.isPreload = false;


						var startTime = moment(videos[0].time);
						var currentTime = moment(date);
						
						this.$refs.vid.currentTime = currentTime.diff(startTime, 'seconds');
						//this.$refs.vid.load();
						//this.$refs.vid.play();
					}
				})
				.catch(error => {

					app.processingPostRequest = false;

					app.error = true;
					console.error('Ошибка получение видео архива: ', error)
				});

			},
			log: function(string) {
				console.log(Date() + ': ' + string)
			}
		}                                          
	}
</script>