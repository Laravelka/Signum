<template>
    <v-container
		class="text-center"
		fill-height
		style="height: calc(100vh - 100px);" 
		v-if="loading"
		fluid
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
			</v-col>
		</v-row>
	</v-container>
	<v-container v-else fluid>
		<v-row justify="center">
			<v-col xs="12" lg="12" xl="12">
				<v-card raised elevation="4">
					<div
						id="stream"
						class="is_overlay"
					>
						<div class="priview" v-if="isPreload && !vidError">
							<v-progress-circular indeterminate color="white"></v-progress-circular>
						</div>
						<div class="priview" v-else-if="isProcessing && !vidError">
							<v-progress-circular size="50" :color="progressColor" :value="preparationPercentage"
								v-if="preparationPercentage !== undefined && preparationPercentage !== null"
							>
								 {{ preparationPercentage }}
							</v-progress-circular>
							<v-progress-circular v-else indeterminate color="primary"></v-progress-circular>
						</div>
						<div
							v-else-if="vidError && !isProcessing && !isPlay"
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
						<div id="progress-time">
							<div ref="progress" id="progress-time-item" @click="onClickProgress"></div>
						</div>
						<video
							v-show="!vidError"
							ref="vid"
							:title="monitor.name"
							:src="videoSrc"
							webkit-playsinline
							playsinline
							autoplay
							muted
							v-on:loadeddata="loadedData"
							v-on:error="videoError"
							v-on:ended="onVideoEnded"
							v-on:play="onVideoPlay"
							v-on:pause="onVideoPause"
							v-on:abort="onVideoAbort"
							v-on:timeupdate="onVideoTimeupdate"
							v-on:seeked="onVideoSeeked"
						></video>
						<div id="videoRate">
							<div class="text-center">
								<v-menu open-on-hover top offset-y>
									<template v-slot:activator="{ on, attrs }">
										<v-btn
											text
											dark
											v-bind="attrs"
											v-on="on"
											:disabled="isPreload || !isPlay"
										>
											X{{ currentRate }}
										</v-btn>
									</template>
									<v-list>
										<v-list-item @click="rateUp(32.0)">
											<v-list-item-title>X32</v-list-item-title>
										</v-list-item>
										<v-list-item @click="rateUp(16.0)">
											<v-list-item-title>X16</v-list-item-title>
										</v-list-item>
										<v-list-item @click="rateUp(8.0)">
											<v-list-item-title>X8</v-list-item-title>
										</v-list-item>
										<v-list-item @click="rateUp(4.0)">
											<v-list-item-title>X4</v-list-item-title>
										</v-list-item>
										<v-list-item @click="rateUp(2.0)">
											<v-list-item-title>X2</v-list-item-title>
										</v-list-item>
										<v-list-item @click="rateUp(1.0)">
											<v-list-item-title>X1</v-list-item-title>
										</v-list-item>
									</v-list>
								</v-menu>
							</div>
						</div>
						<div id="fullScreen">
							<v-btn icon :disabled="isPreload || vidError" @click="fullScreen(true)">
								<v-icon size="24">mdi-arrow-expand-all</v-icon>
							</v-btn>
						</div>
					</div>
					<v-card-title :style="{padding: '12px', 'margin-top': '-10px'}">
						<v-row justify="space-between">
							<v-col cols="6">
								<v-btn
									icon
									small
									elevation="0"
									color="text--bold"
									@click="playStream"
									:disabled="isStream && isPreload"
								>
									<v-img v-if="isStream" height="24" width="24" src="/images/live.png"></v-img>
									<v-icon v-else>mdi-refresh</v-icon>
								</v-btn>
								<v-btn icon :disabled="isPreload || !isPlay" @click="rewind(10)">
									<v-icon>mdi-rewind-10</v-icon>
								</v-btn>
								<v-btn icon :disabled="isPreload || !isPlay" @click="forward(10)">
									<v-icon>mdi-fast-forward-10</v-icon>
								</v-btn>
							</v-col>
							<v-col cols="6" class="d-flex justify-end">
								<v-btn icon v-if="!isPlay" :disabled="isPreload || vidError || timelineDisabled" @click="playVideo()">
									<v-icon>mdi-play</v-icon>
								</v-btn>
								<v-btn icon v-else :disabled="isPreload || vidError || timelineDisabled" @click="pauseVideo()">
									<v-icon>mdi-pause</v-icon>
								</v-btn>
								<a :href="monitor.server + currentVideo.href" :disabled="isStream || isPreload || vidError || timelineDisabled" download class="v-btn v-btn--flat v-btn--icon v-btn--round theme--dark v-size--default">
									<span class="v-btn__content"><i data-v-35a3a9bf="" aria-hidden="true" class="v-icon notranslate mdi mdi-cloud-download theme--dark"></i></span>
								</a>
								<v-btn icon :disabled="isPreload" @click="snapshot(isStream)">
									<v-icon>mdi-camera</v-icon>
								</v-btn>
								<v-datetime :disabled="isPreload || timelineDisabled" v-model="timeNowCalendar"></v-datetime>
								<v-btn icon @click="fullScreen(false)">
									<v-icon>{{ isFullScreen ? 'mdi-arrow-all' : 'mdi-arrow-expand-all' }}</v-icon>
								</v-btn>
							</v-col>
						</v-row>
					</v-card-title>
					<v-card-actions class="flex-column justify-center align-items-center" :style="{'margin-top': (window.width < 655 ? '-30px' : '-60px')}">
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
								Архив пуст
							</div>
							<div class="load-timeline" v-else-if="timelineLoader">
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
	</v-container>
</template>
<style scoped>
	#videoRate {
		position: absolute;
		bottom: 5px;
		left: 5px;
	}
	
	#fullScreen {
		position: absolute;
		bottom: 5px;
		right: 5px;
	}
	
	#progress-time {
		background: rgba(0, 0, 0, 0.2);
		position: absolute;
		height: 4px;
		width: 100%;
		bottom: 0;
		left: 0;
	}
	
	#progress-time-item {
		background: #3f51b5;
		position: absolute;
		height: 4px;
		width: 0%;
		bottom: 0;
		left: 0;
	}
	
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
		overflow: hidden;
		height: 54vw;
		min-height: 44vw;
		position: relative;
	}
	
	#stream video {
		width: 100%;
		display: block;
		object-fit: cover!important;
	}
	
	@media (min-width: 1280px) {
		#stream {
			height: 44vw!important;
		}
	}
	
	@media (max-width: 380px) {
		#stream {
			height: 53vw!important;
		}
	}
	
	/*
	@media (max-width: 360px) {
		#stream {
			height: 200px!important;
		}
	}
	
	@media (max-width: 600px) {
		#stream {
			height: 240px!important;
		}
	}

	@media (min-width: 600px) and (max-width: 960px) {
		#stream {
			height: 420px!important;
		}
	}

	@media (min-width: 960px) {
		#stream {
			height: 520px!important;
		}
	}
	
	@media (min-width: 1280px) {
		#stream {
			height: 615px!important;
		}
	}
	
	@media (min-width: 1660px) {
		#stream {
			height: 615px!important;
		}
	}
	
	@media (min-width: 1920px) {
		#stream {
			height: 1000px!important;
		}
	}
	*/
	
	.darkening {
		background: rgba(0, 0, 0, 0.3);
	}

	.is_overlay {
		display: block;
		width: 100%;
		height: 100%;
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
	import {
		ucFirst,
		fullScreen,
		fullScreenCancel
	} from '@/js/helpers/functions';
	import moment from 'moment';
	import lodash from 'lodash';
	import download from 'downloadjs';
	import mobileDetect from 'mobile-detect';

	moment.locale('ru');

	export default {
		data: () => ({
			window: {
				width: 0,
				height: 0,
			},
			currentRate: 1,
			server: "",
			videos: [],
			isFullScreen: false,
			currentVideo: {},
			onSetNewTimelineCenterFlag: false,
			progressColor: 'white',
			timeNow: moment().format('LTS'),
			videoSrc: false,
			title: 'Монитор',
			error: false,
			vidError: false,
			loaded: false,
			isPlay: false,
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
					max: moment().add(1, 'day'),
				},
				downloadeditem: null,
				center: null,
				average: null,
				play: false
			},
			timelineLoader: true,
			timelineDisabled: false,
			calendar: {
				isOpen: false,
				time: moment().format('hh:mm:ss'),
				date: moment().format('YYYY-MM-DD')
			},
			mobileDetect: null,
			timeNowCalendar: null,
		}),
		created() {
			var app = this;
			var now = moment();

			this.archive.dateNow = now;
			this.archive.dateString = ucFirst(now.format('dddd DD-MM-YYYY'));

			this.mobileDetect = new mobileDetect(navigator.userAgent);
	
			window.addEventListener('resize', this.updateWidth);
			this.updateWidth();
			
			this.$root.$emit('hide-object', ['bottomNavigation']);
			this.$root.$on('setVideosLabels', (items) => {
				app.timelineLoader = false;

				if (items[0].start) {
					app.moveTimelineTo(moment(items[0].start));
				}
			});

			this.$root.$on('clickedRefreshButton', function(data) {
				app.reloadPage();
			});

			this.$root.$on('okCalendar', function(data) {
				const date = moment(new Date(data.join('T')));

				app.moveTimelineTo(date);
				app.archive.dateNow = date;
				app.archive.dateString = ucFirst(date.format('dddd DD-MM-YYYY'));
				app.changeVideoDate(date);

				console.log(app.currentVideo);
			});

			this.changeVideoDateThottled = _.debounce(this.changeVideoDate, 500); // Ограничиеваем вызов раз в 500 мс
			this.changeArchiveTimeStringThottled = _.throttle(this.changeArchiveTimeString, 200)
		},
		mounted() {
			var app = this;

			this.axios.post('/monitors/get', {
					id: app.$route.params.id
				})
				.then(response => {
					app.loading = false;
					app.monitor = response.data;
					app.title = app.monitor.name;
					app.isStream = true;

					if (app.mobileDetect.os() == 'iOS') {
						app.videoSrc = app.monitor.server + app.monitor.streams[0].replace(/cam(\d+)\/s.mp4/ui, 'cam$1hls\/s.m3u8').replace(/mp4/ui, 'hls');
					} else {
						app.videoSrc = app.monitor.server + app.monitor.streams[0];
					}

					app.$root.$emit('active-panel', {
						id: -1,
						title: app.title
					});

					app.axios.post('/videos/getByMonitor', {
							monitor_id: app.monitor.mid
						})
						.then(response => {
							const data = response.data;

							if (data.total < 1) {
								this.timelineDisabled = true;
							}
							this.$refs['timeline'].setVideosLabels(data.videos);
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
			rateUp(rate = 2) {
				this.$refs['vid'].playbackRate = this.currentRate = rate;
			},
			rewind(seconds) {
				this.$refs['vid'].currentTime -= seconds;
			},
			forward(seconds) {
				this.$refs['vid'].currentTime += seconds;
			},
			onClickProgress(e) {
				let posX = e.clientX - 8;
				let percent = (posX * 100) / 800;
				
				console.log(percent);
				
				// this.$refs.progress.style.width = percent + '%';
				
				// this.$refs['vid'].currentTime = (percent * Math.round(this.$refs['vid'].duration)) / 100;
			},
			updateWidth() {
				this.window.width = window.innerWidth;
				this.window.height = window.innerHeight;
			},
			downloadCurrentVideo() {
				var xhr = new XMLHttpRequest();
				xhr.open("GET", this.monitor.server + this.currentVideo.href, true);
				xhr.responseType = 'blob';
				xhr.onwload = function(e) {
					download(xhr.response, this.currentVideo.href.split('/')[5], "video/mp4");
				}
				xhr.send();
			},
			playVideo() {
				this.vidError = false;
				this.$refs['vid'].play();
				this.isPlay = true;
			},
			pauseVideo() {
				this.isPlay = false;
				this.$refs['vid'].pause();
			},
			snapshot(isStream) {
				this.downloadImg(this.monitor.server + this.monitor.snapshot, this.timeNow);
			},
			downloadImg(url, name) {
				var xhr = new XMLHttpRequest();
				xhr.open("GET", url, true);
				xhr.responseType = 'blob';
				xhr.onload = function(e) {
					download(xhr.response, name + ".jpg", "image/jpeg");
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
				this.isPlay = false;
			},
			onVideoPlay(event) {
				this.srcChangedFlag = false;
				console.log(this.isProcessing);
				this.isProcessing = false;
				this.preparationPercentage = 0;

				if (this.currentVideo.playStartTime === undefined) {
					this.currentVideo.playStartTime = event.target.currentTime;
				}

				if (this.$refs['timeline']) {
					this.$refs['timeline'].play();
				}
				this.isPreload = false;
				this.isStream = false;
				this.isPlay = true;
			},
			onVideoSeeked(event) {
				this.currentVideo.playStartTime = event.target.currentTime;
			},
			onVideoPause(event) {
				console.log('Pause: ', event);

				this.isPlay = false;

				if (this.$refs['timeline']) {
					this.$refs['timeline'].stop();
				}
			},
			onVideoAbort(event) {
				console.error('Abort: ', event);

				if (this.$refs['timeline']) {
					this.$refs['timeline'].stop();
				}
				this.isPlay = false;
			},
			onVideoTimeupdate(event) {
				const percent = 100 * Math.round(event.target.currentTime) / Math.round(event.target.duration);
				
				this.$refs.progress.style.width = percent + '%';
					
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

						// this.log('Время обновилось: ' + event.target.currentTime);
						var currentTime = moment(this.currentVideo.time).add(event.target.currentTime / 1000, 'seconds');
						currentTime.local();
						currentTime = currentTime.add(event.target.currentTime, 'seconds');
						// this.log('Время обновилось: ' + currentTime.toString(), currentTime);
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
						var newSrc = `${window.baseUrl}/get_video/?scheme=${encodeURIComponent(match[1])}&host=${encodeURIComponent(match[2])}&filepath=${encodeURIComponent(this.currentVideo.href)}`;
						if (app.videoSrc != newSrc) {
							this.changeVideoSrc(newSrc);
						}
						this.$refs.vid.load();
					}
				}
				this.isPlay = false;
			},
			onPauseOnTimelineMove() {
				this.$refs.vid.pause();
			},
			playStream() {
				if (this.mobileDetect.os() == 'iOS') {
					this.videoSrc = this.monitor.server + this.monitor.streams[0].replace(/cam(\d+)\/s.mp4/ui, 'cam$1hls\/s.m3u8').replace(/mp4/ui, 'hls');
				} else {
					this.videoSrc = this.monitor.server + this.monitor.streams[0];
				}
				this.isStream = true;
				this.isPreload = true;
				this.vidError = false;
			},
			fullScreen(isReal = false) {
				if (isReal)
				{
					fullScreen(this.$refs.vid);
				}
				else
				{
					this.$root.$emit((this.isFullScreen ? 'show-object' : 'hide-object'), ['appBar', 'drawer']);
					this.isFullScreen = !this.isFullScreen;
				}
			},
			reloadPage() {
				location.reload();
			},
			onSetNewTimelineCenter: function() {

				this.onSetNewTimelineCenterFlag = true;
				this.changeVideoDateThottled(this.timelineParams.center);

				if (this.timelineParams.center) {
					this.changeArchiveTimeStringThottled(this.timelineParams.center);
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
				var src = `${window.baseUrl}/get_video/prepare/?scheme=${encodeURIComponent(match[1])}&host=${encodeURIComponent(match[2])}&filepath=${encodeURIComponent(this.currentVideo.href)}`;

				this.axios.get(src, {})
					.then(response => {
						this.preparationPercentage = response.data.preparationPercentage.toFixed();

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
			moveTimelineTo: function(time) {
				this.$refs['timeline'].moveTimelineTo(time);
			},
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

				app.axios.post('/videos/getByMonitorId', {
						monitor_id: app.monitor.mid,
						date: date
					})
					.then(response => {
						app.processingPostRequest = false;

						const videos = response.data.videos;
						this.videos = response.data.videos;

						if (response.data.total < 1) {
							app.vidError = true;
							app.isPreload = false;
							app.timelineLoader = false;
						} else {
							app.archive.data = videos;
							app.isStream = false;
							app.isPreload = false;

							this.currentVideo = videos[videos.length - 1];

							var startTime = moment(videos[videos.length - 1].time) /*.utcOffset(0, true)*/ ;
							var currentTime = moment(date) /*.utcOffset(0, true)*/ ;
							startTime.local();
							currentTime.local();

							if (currentTime.diff(startTime, 'seconds') < 0) {
								currentTime = startTime;
							}

							this.server = response.data.server;
							var match = this.server.match(/^(?:(https|http):\/\/)?([^:\/\\]+(?::\d{1,5})?)$/);
							var newSrc = `${window.baseUrl}/get_video/?scheme=${encodeURIComponent(match[1])}&host=${encodeURIComponent(match[2])}&filepath=${encodeURIComponent(videos[videos.length - 1].href)}`;

							if (app.videoSrc != newSrc) {
								this.changeVideoSrc(newSrc);
							}
							// this.$refs.vid.load();
							this.$refs.vid.currentTime = currentTime.diff(startTime, 'seconds');
						}
						this.$root.$emit('setVideos', this.videos);
					})
					.catch(error => {
						app.error = true;
						app.processingPostRequest = false;

						console.error('Ошибка получение видео архива: ', error)
					});

			},
			prepareVideo(video) {
				var match = this.server.match(/^(?:(https|http):\/\/)?([^:\/\\]+(?::\d{1,5})?)$/);
				var src = `${window.baseUrl}/get_video/prepare/?scheme=${encodeURIComponent(match[1])}&host=${encodeURIComponent(match[2])}&filepath=${encodeURIComponent(video.href)}`;

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