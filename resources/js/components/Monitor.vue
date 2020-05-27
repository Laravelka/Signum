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
			<v-col xs="12" lg="12" xl="12">
				<v-card raised elevation="5" :style="{'margin-top': '80px'}">
					<div
						id="stream"
						class="is_overlay"
					>
						<div class="priview" v-if="isPreload">
							<v-progress-circular indeterminate color="white"></v-progress-circular>
						</div>
						<div class="priview" v-else-if="isProcessing">
							<v-progress-circular size="50" :color="progressColor" :value="preparationPercentage"
								v-if="preparationPercentage !== undefined && preparationPercentage !== null"
							>
								 {{ preparationPercentage }}
							</v-progress-circular>
							<v-progress-circular v-else indeterminate color="primary"></v-progress-circular>
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
							v-on:ended="onVideoEnded"
							v-on:play="onVideoPlay"
							v-on:pause="onVideoPause"
							v-on:abort="onVideoAbort"
							v-on:timeupdate="onVideoTimeupdate"
							v-on:seeked="onVideoSeeked"
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
								<v-btn icon :disabled="isPreload || isStream" @click="calendar.isOpen = !calendar.isOpen">
									<v-icon>mdi-calendar</v-icon>
								</v-btn>
								<v-btn icon @click="fullScreen" :disabled="isPreload">
									<v-icon>mdi-arrow-expand-all</v-icon>
								</v-btn>
							</v-col>
						</v-row>
					</v-card-title>
					<v-card-actions class="flex-column justify-center align-items-center" :style="{'margin-top': '50px'}">
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
						<v-row :style="{position: 'relative', width: '100%'}">
							<div class="load-timeline" v-if="timelineDisabled">
								<v-progress-circular
									:size="30"
									:width="3"
									color="white"
									indeterminate
								></v-progress-circular>
							</div>
							<timeline
								:params="timelineParams"
								@setNewTimelineCenter="onSetNewTimelineCenter()"
								@pauseOnTimelineMove="onPauseOnTimelineMove()"
								ref="timeline"
							></timeline>
						</v-row>
					</v-card-actions> 
				</v-card>
			</v-col>
		</v-row>
		<v-dialog v-model="calendar.isOpen">
			<v-time-picker
				v-model="timeNow"
				use-seconds
			>
				<v-spacer></v-spacer>
				<v-btn text @click="calendar.isOpen = false">Отмена</v-btn>
				<v-btn text @click="calendar.isOpen = false">Открыть</v-btn>
			</v-time-picker>
		</v-dialog>
	</v-container>
</template>
<style scoped>
	.load-timeline {
		display: flex;
		align-items: center;
    	justify-content: center;
		position: absolute;
		background: rgba(0, 0, 0, 0.3);
		min-height: 61px;
		width: 100%;
		z-index: 2;
	}
	
	#stream {
		width: 100%;
		height: 210px;
		overflow: hidden;
	}
	
	@media (max-width: 600px) {
		#stream {
			height: 230px!important;
		}
	}
	
	@media (min-width: 600px) and (max-width: 960px) {
		#stream {
			height: 400px!important;
		}
	}
	
	@media (min-width: 960px) {
		#stream {
			height: 470px!important;
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
	
	.timeline-bottom {
		position: absolute;
		bottom: 0;
		width: 100%;
		left: 0;
		border-radius: 0;
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
			server: "",
			videos: [],
			currentVideo: {},
			onSetNewTimelineCenterFlag: false,
			progressColor: 'white',
			timeNow: moment().format('LTS'),
			videoSrc: false,
			title: 'Монитор',
			error: false,
			vidError: false,
			loaded: false,
			isStream: false,
			isPreload: true,
			isProcessing: false,
			preparationPercentage: 0,
			loading: true,
			monitor: null,
			archive: {
				data: null,
				dateNow: null,
				dateString: null,
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
			},
			timelineDisabled: true,
			calendar: { 
				isOpen: false,
				time: moment().format('hh:mm:ss'),
				date: moment().format('YYYY-MM-DD')
			}
		}),
		watch: {
			'archive.dateNow': function(newVal, oldVal) {
				console.log(moment(newVal).format('hh:mm:ss'), moment(newVal).format('YYYY-MM-DD'))
				/*this.isPreload = false;
				this.isStream = false;*/
			}	
		},
		created() {
			var app = this;
			var now = moment();
			
			app.archive.dateNow = now;
			app.archive.dateString = ucFirst(now.format('dddd DD-MM-YYYY'));
			
			app.$root.$emit('hide-object', ['bottomNavigation']);
			app.$root.$on('setVideosLabels', (e) => {
				app.timelineDisabled = false;
			});
			this.changeVideoDateThottled = _.debounce(this.changeVideoDate, 500); // Ограничиеваем вызов раз в 500 мс
			this.changeArchiveTimeStringThottled =  _.throttle(this.changeArchiveTimeString, 200)
		},
		mounted() {
			var app = this;
			
			app.axios.post('/monitors/get', {id: app.$route.params.id})
			.then(response => {
				app.loading = false;
				app.monitor = response.data;
				app.title = app.monitor.name;
				app.isStream = true;
				app.videoSrc = app.monitor.server + app.monitor.streams[0];
				app.$root.$emit('active-panel', {id: -1, title: app.title});
				
				app.axios.post('/videos/getByMonitor', {monitor_id: app.monitor.mid})
				.then(response => {
					this.$refs['timeline'].setVideosLabels(response.data.response.videos);
				})
				.catch(error => {
					app.error = true;
					app.loading = false;
					
					console.error('Ошибка получения видео: ', error);
				});
				
			})
			.catch(error => {
				app.error = true;
				app.loading = false;
				
				console.error('Ошибка получения камеры: ', error);
			});

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
			loadedData() {
				this.loaded = true;
				this.isPreload = false;
				this.$refs.vid.style.display = 'block';
				this.$refs.vid.play();
			},
			videoError(event) {
				console.error('Error: ', event);
				
				this.isPreload = false; 
				this.vidError = true;
			},
			onVideoPlay(event) {
				this.srcChangedFlag = false;
				console.log(this.isProcessing);
				this.isProcessing = false;
				this.preparationPercentage = 0;
				// Хранит время воспроизведения, чтобы можно было определить когда оно 
				if (this.currentVideo.playStartTime === undefined) {
					this.currentVideo.playStartTime = event.target.currentTime;
				}

				if (this.$refs['timeline']) {
					this.$refs['timeline'].play();
				}
				this.isPreload = false;
				this.isStream = false;
			},
			onVideoSeeked(event) {
				this.currentVideo.playStartTime = event.target.currentTime;
			},
			onVideoPause(event) {
				console.log('Pause: ', event);
				
				if (this.$refs['timeline']) {
					this.$refs['timeline'].stop();
				}
			},
			onVideoAbort(event) {
				console.error('Abort: ', event);
				
				if (this.$refs['timeline']) {
					this.$refs['timeline'].stop();
				}
			},
			onVideoTimeupdate(event) {
				if (!this.onSetNewTimelineCenterFlag) {
					if (this.currentVideo && this.currentVideo.time) {

						if ((event.target.currentTime - this.currentVideo.playStartTime) > 5) {
							var index = this.videos.indexOf(this.currentVideo);
							if (index !== -1 && index !== 0) {
								if (this.videos[index - 1] && !this.videos[index - 1].preparedFlag) {
									this.prepareVideo(this.videos[index - 1]);
									this.videos[index - 1].preparedFlag = true;
								}
							}
						}

						this.log('Время обновилось: ' + event.target.currentTime);
						var currentTime = moment(this.currentVideo.time).add(event.target.currentTime / 1000, 'seconds');
						currentTime.local();
						currentTime = currentTime.add(event.target.currentTime, 'seconds');
						this.log('Время обновилось: ' + currentTime.toString());
						this.moveTimelineTo(currentTime);
						this.archive.dateNow = currentTime;
						this.timeNow = currentTime.format('LTS');
						this.archive.dateString = ucFirst(currentTime.format('dddd DD-MM-YYYY'));
						
					} 
				}
			},
			onVideoEnded() {
				var app = this;

				var index = this.videos.indexOf(this.currentVideo);
				if (index !== -1 && index !== 0) {
					if (this.videos[index - 1]) {
						this.currentVideo = this.videos[index - 1];
						var match = this.server.match(/^(?:(https|http):\/\/)?([^:\/\\]+(?::\d{1,5})?)$/);
						var newSrc = `/get_video/?scheme=${encodeURIComponent(match[1])}&host=${encodeURIComponent(match[2])}&filepath=${encodeURIComponent(this.currentVideo.href)}`;
						if (app.videoSrc != newSrc) {
							this.changeVideoSrc(newSrc);
							//app.videoSrc = newSrc;
						}
						this.$refs.vid.load();
					}

				}

			},
			onPauseOnTimelineMove() {
				this.$refs.vid.pause();
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

			fullScreen() {
				fullScreen(this.$refs.vid);
			},
			reloadPage() {
				location.reload();
			},
			onSetNewTimelineCenter: function () {

				this.onSetNewTimelineCenterFlag = true;
				this.changeVideoDateThottled(this.timelineParams.center);

				if (this.timelineParams.center) {

					this.changeArchiveTimeStringThottled(this.timelineParams.center);
					// var time = moment(this.timelineParams.center);
					// this.archive.dateNow = time;
					// this.timeNow = time.format('LTS');
					// this.archive.dateString = ucFirst(time.format('dddd DD-MM-YYYY'));
				}
			},
			changeVideoSrc(newSrc) {
				this.isProcessing = true;
				console.log(this.isProcessing);
				this.preparationPercentage = 0;
				this.videoSrc = newSrc;
				this.srcChangedFlag = true;
				this.updatePreparationPercentage();
			},
			updatePreparationPercentage() {
				var match = this.server.match(/^(?:(https|http):\/\/)?([^:\/\\]+(?::\d{1,5})?)$/);
				var src = `https://ex-coin.space/get_video/prepare/?scheme=${encodeURIComponent(match[1])}&host=${encodeURIComponent(match[2])}&filepath=${encodeURIComponent(this.currentVideo.href)}`;

				this.axios.get(src, {})
				.then(response => {
					this.preparationPercentage = response.data.preparationPercentage.toFixed();
					// debugger;
					if (this.isProcessing) {
						this.updatePreparationPercentage();
					}
				});

			},
			changeArchiveTimeString: function(center) {
				this.log("Время изменилось");
				var time = moment(this.timelineParams.center);
				this.archive.dateNow = time;
				this.timeNow = time.format('LTS');
				this.archive.dateString = ucFirst(time.format('dddd DD-MM-YYYY'));
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

						var startTime = moment(videos[videos.length - 1].time)/*.utcOffset(0, true)*/;
						var currentTime = moment(date)/*.utcOffset(0, true)*/;
						startTime.local();
						currentTime.local();


						if (currentTime.diff(startTime, 'seconds') < 0) {
							currentTime = startTime;
						}

						this.server = response.data.server;
						var match = this.server.match(/^(?:(https|http):\/\/)?([^:\/\\]+(?::\d{1,5})?)$/);
						var newSrc = `/get_video/?scheme=${encodeURIComponent(match[1])}&host=${encodeURIComponent(match[2])}&filepath=${encodeURIComponent(videos[videos.length - 1].href)}`;
						//app.videoSrc = response.data.server + videos[0].href;
						if (app.videoSrc != newSrc) {
							this.changeVideoSrc(newSrc);
							//app.videoSrc = newSrc;
						}
						this.$refs.vid.load();
						this.$refs.vid.currentTime = currentTime.diff(startTime, 'seconds');
					}
				})
				.catch(error => {
					app.error = true;
					app.processingPostRequest = false;
					
					console.error('Ошибка получение видео архива: ', error)
				});

			},
			prepareVideo(video) {
				var match = this.server.match(/^(?:(https|http):\/\/)?([^:\/\\]+(?::\d{1,5})?)$/);
				var src = `/get_video/prepare/?scheme=${encodeURIComponent(match[1])}&host=${encodeURIComponent(match[2])}&filepath=${encodeURIComponent(video.href)}`;

				this.axios.get(src, {})
				.then(response => {
					// Не интересует. Нужно лишь отправить.
				});
			},
			log: function(string) {
				console.log(Date() + ': ' + string);
			},
		}                                          
	}
</script>