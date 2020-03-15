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
							v-on:play="onVideoPlay"
							v-on:pause="onVideoPause"
							v-on:abort="onVideoAbort"
							v-on:timeupdate="onVideoTimeupdate"
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
							<span>{{ archive.dateString }}</span>
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
			videos: [],
			currentVideo: {},
			onSetNewTimelineCenterFlag: false,

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
					min: moment().subtract(7, 'day'),
					max: moment().add(7, 'day'),
				},
				downloadeditem: null, 
				// center: moment().format(moment.HTML5_FMT.DATETIME_LOCAL_SECONDS),
				// average: moment(),
				center: null,
				average: null,
				play: false
			}
		}),
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
				// app.videoSrc = app.monitor.server + app.monitor.streams[0];
				app.$root.$emit('active-panel', {id: -1, title: app.title});
				
				app.axios.post('/videos/getByMonitor', {monitor_id: app.monitor.mid})
				.then(response => {
					console.log('videos:', response);
				})
				.catch(error => {
					console.error('Videos error: ', error);
				});
				app.loading = false;
			})
			.catch(error => {
				app.error = true;
				app.loading = false;
				
				console.error('Ошибка получения камеры: ', error);
			});
			this.changeVideoDateThottled = _.debounce(this.changeVideoDate, 500); // Ограничиеваем вызов раз в 500 мс

		},
		mounted() {
			// this.updateTime();
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

			onVideoPlay(event) {
				console.error(event);
				
				if (this.$refs['timeline']) {
					this.$refs['timeline'].play();
				}
			},
			onVideoPause(event) {
				console.error(event);
				
				if (this.$refs['timeline']) {
					this.$refs['timeline'].stop();
				}
			},
			onVideoAbort(event) {
				console.error(event);
				
				if (this.$refs['timeline']) {
					this.$refs['timeline'].stop();
				}
			},
			onVideoTimeupdate(event) {

				if (!this.onSetNewTimelineCenterFlag) {
					if (this.currentVideo) {
						this.log('Время обновилось: ' + event.timeStamp);
						var currentTime = moment(this.currentVideo.time).utcOffset(0, true).add(event.timeStamp / 1000, 'seconds');
						this.log('Время обновилось: ' + currentTime);
						
						this.archive.dateNow = currentTime;
						this.timeNow = currentTime.format('LTS');
						this.archive.dateString = ucFirst(currentTime.format('dddd DD-MM-YYYY'));
						
						this.moveTimelineTo(currentTime);
					}

				}

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
			// getVideoByDate(date) {
			// 	var app = this;
				
			// 	app.vidError = false;
			// 	app.isStream = false;
			// 	app.isPreload = true;
			// 	// app.$refs.vid.src = '';
				
			// 	app.axios.post('/videos/getByMonitorId', {monitor_id: app.monitor.mid, date: date})
			// 	.then(response => {
			// 		const videos = response.data.videos;
					
			// 		if (response.data.total < 1)
			// 		{
			// 			app.vidError = true;
			// 			app.isPreload = false;
			// 		}
			// 		else
			// 		{
			// 			app.archive.data = videos;
			// 			app.videoSrc = response.data.server + videos[0].href;
			// 		}
			// 	})
			// 	.catch(error => {
			// 		app.error = true;
			// 		console.error('Ошибка получение видео архива: ', error)
			// 	});
			// },
			fullScreen() {
				fullScreen(this.$refs.vid);
			},
			reloadPage() {
				location.reload();
			},
			onSetNewTimelineCenter: function () {

				this.onSetNewTimelineCenterFlag = true;
				this.changeVideoDateThottled(this.timelineParams.center);
			},
			moveTimelineTo: function (time) {
				this.$refs['timeline'].moveTimelineTo(time);
			},

			// changeVideoDateThottled: function() {},
			changeVideoDate: function(date) {
				var app = this;
				
				app.onSetNewTimelineCenterFlag = false;
				app.vidError = false;
				app.isStream = false;
				app.isPreload = true;
				
				if (app.processingPostRequest) {
					app.changeVideoDateThottled(date);
				}

				app.processingPostRequest = true;

				app.axios.post('/videos/getByMonitorId', {monitor_id: app.monitor.mid, date: date})
				.then(response => {

					//console.log('Answer time: '  + (new Date));

					app.processingPostRequest = false;

					const videos = response.data.videos;
					this.videos = response.data.videos;

					if (response.data.total < 1) 
					{
						app.vidError = true;
						app.isPreload = false;
					} 
					else 
					{
						app.archive.data = videos;
						app.isStream = true;
						app.isPreload = false;

						this.currentVideo = videos[videos.length - 1];

						var startTime = moment(videos[videos.length - 1].time).utcOffset(0, true);
						var currentTime = moment(date).utcOffset(0, true);

						if (currentTime.diff(startTime, 'seconds') < 0) {
							currentTime = startTime;
						}

						var match = response.data.server.match(/^(https?)?:\/\/([^:\/\\]+(?::\d{1,5}))?$/);

						var newSrc = `/get_video/?scheme=${encodeURIComponent(match[1])}&host=${encodeURIComponent(match[2])}&filepath=${encodeURIComponent(videos[videos.length - 1].href)}`;

						//app.videoSrc = response.data.server + videos[0].href;
						if (app.videoSrc != newSrc) {
							app.videoSrc = newSrc;
						}
						
						this.$refs.vid.load();
						this.$refs.vid.currentTime = currentTime.diff(startTime, 'seconds');

						console.log('Вывод инфы начальное время видоса, выбираемое время, установленное время видоса, ральное $refs.vid.currentTime время видоса');
						console.log(moment(date).utcOffset(0, true));
						console.log(startTime);
						console.log(currentTime);
						console.log(this.$refs.vid.currentTime);


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