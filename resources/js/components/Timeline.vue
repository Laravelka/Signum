<template>
	<div id='visualization' ref="visualization" style="width: 100%;" @wheel="onWhell" v-bind:class="{dark: $vuetify.theme.dark}"></div>
</template>
<script>
	import moment from 'moment';
	import config from '@/js/config';
	import download from 'downloadjs';
	import { DataSet } from "vis-data/peer";
	import { Timeline } from "vis-timeline/peer";
	import { ucFirst, fullScreen, fullScreenCancel } from '@/js/helpers/functions';

	moment.locale('ru');
	
	export default {
		name: 'timeline',		
		props: ['params'],
		data: function () {
			var now = +(new Date()); 
			return {
				timelineinstance: null,
			};
		},
		methods: {
			updateDownloaded: function () {
				if (this.params.downloadeditem) {
					var items = new DataSet([{
						start: this.params.downloadeditem.start,
						end: this.params.downloadeditem.end,
						id: 1,
						type: 'background',
					}]);
					this.timelineinstance.setItems(items);

				} else {
					this.timelineinstance.setItems(new DataSet([]));
				}
			},
			onWhell: function(event) {
				event.preventDefault();
			},
			moveTimelineTo: function (time) {
				this.timelineinstance.moveTo(time, {animation: false});
				this.params.play = false;
			},
			/**
			 * Принимает два массива видосов на случай, если будет список ДО и ПОСЛЕ
			 * какого-то времени
			 */
			setVideosLabels: function (videos1, videos2) {
				var items = [];
				videos1 = videos1 || [];
				videos2 = videos2 || [];

				videos1.forEach((item, key,i) => {
					var start = moment(item.time);
					var end = moment(item.end);
					var type = 'background'
					var style = 'background: #3f51b5; border-radius: .50rem!important;';
					items.push({
						start: start,
						end: end,
						type: type,
						style: style,
						selectable: true,
					});
				});

				videos2.forEach((item, key,i) => {
					var start = moment(item.time);
					var end = moment(item.end);
					var type = 'background'
					var style = 'background: #3f51b5; border-radius: .50rem!important;';
					items.push({
						start: start,
						end: end,
						type: type,
						style: style,
						selectable: true,
					});
				});
				var itemsSet = new DataSet(items);
				
				this.timelineinstance.setItems(itemsSet);
				
				this.$root.$emit('setVideosLabels', items);
			},
			play:function() {
				this.params.play = true;
			},
			stop: function () {
				this.params.play = false;
			}
		},
		computed: {
		},
		mounted(argument) {
			var container = this.$refs["visualization"];
			
			console.log('Конфиг: ', config);
			
			if (this.params.downloadeditem) {
				var items = new DataSet([this.params.downloadeditem]);
			} else {
				var items = new DataSet([]);
			}

			if (!this.params.average) {
				if (this.params.center) {
					this.params.average = moment(this.params.center);
				} else {
					this.params.average = moment();
					this.params.center = this.params.average.format(moment.HTML5_FMT.DATETIME_SECONDS);
				}

			}
			var start = moment((this.params.range.min + this.params.range.max) / 2).subtract(1, 'hours');
			var end = moment((this.params.range.min + this.params.range.max) / 2).add(1, 'hours');

			var options = {
				min: this.params.range.min,
				max: this.params.range.max,
				start: start,
				end: end,
				zoomMin: 60000,
				zoomMax: 86400000,
				showCurrentTime: false,
			};
			this.timelineinstance = new Timeline(container, items, options);
			// Вертикальная полоса, которая будет центроваться после
			this.timelineinstance.addCustomTime(this.params.average, "custome-type-center"); 
			this.timelineinstance.moveTo(this.params.average, {animation: false});


			this.timelineinstance.on('rangechange', (properties) => {

				var end = moment(properties.end);
				var start = moment(properties.start);
				var average = moment((end + start)/2);
				this.params.center = average.format(moment.HTML5_FMT.DATETIME_SECONDS);
				this.timelineinstance.setCustomTime(average, "custome-type-center")

				if (properties.byUser) {
					this.$emit('pauseOnTimelineMove');
					this.params.play = "false";
					this.$emit('setNewTimelineCenter');
				}
			});
		},
		created() {
		},
		watch: {
			'params.downloadeditem': function() {
				//debugger;
			}
		}
	}
</script>