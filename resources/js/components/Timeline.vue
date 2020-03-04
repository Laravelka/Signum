<template>

	<div id='visualization' ref="visualization" style="height: 60px; width: 100%;" @wheel="onWhell"></div>

</template>

<style>

</style>
<script>
	import { ucFirst, fullScreen, fullScreenCancel } from '@/js/helpers/functions';
	import moment from 'moment';
	import download from 'downloadjs';

	import { DataSet, Timeline } from 'vis/index-timeline-graph2d';

	moment.locale('ru');
	
	export default {
		name: 'timeline',
		/*
		params = {
			range: {
				min: moment().subtract(1, 'month'),
				max: moment(),
			},
			downloadeditem: {
				start: moment().subtract(2, 'days'),
				end: moment().subtract(1, 'days'),
				id: "timeline-downloaded-item",
				type: 'background',
			},
			center: null,
			play: false

		}
		*/
		
		props: ['params'],
		data: function () {
			var now = +(new Date()); 
			return {
				interval: null, // нужна ли вообще? Переменная для сохранения таймера
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

				this.timelineinstance.moveTo(time);

				// Тут можно сделать обработку того случая, когда движение при произведении. Ну а пока пауза
				this.params.play = false;
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
		watch: {
		},
		mounted: function (argument) {
			var container = this.$refs["visualization"];
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

			var options = {
				min: this.params.range.min,
				max: this.params.range.max,
				end: this.params.average.subtract(1, 'day'),
				end: this.params.average.add(1, 'day'),
				zoomMin: 5000, // Вся полоска отображает минимально ширину в 5 секунд, ближе не приблизить
				showCurrentTime: false,
			};
			this.timelineinstance = new Timeline(container, items, options);
			this.timelineinstance.addCustomTime(this.params.average, "custome-type-center"); // Вертикальная полоса, которая будет центроваться после
			this.timelineinstance.moveTo(this.params.average, {animation: false});

			// /* Костыли, костыли, грёбаные костыли */
			// if (!this.params.center) { 

			// 	var timewindow = this.timelineinstance.getWindow();
			// 	var end = moment(timewindow.end);
			// 	var start = moment(timewindow.start);
			// 	var average = moment((end + start)/2)
			// 	this.timelineinstance.addCustomTime(average, "custome-type-center"); // Вертикальная полоса, которая будет центроваться после

			// } else {

			// 	this.timelineinstance.moveTo(this.params.center, {animation: false});
			// 	this.timelineinstance.addCustomTime(this.params.center, "custome-type-center"); // Вертикальная полоса, которая будет центроваться после

			// }

			// Была идея сделать разные линии для центра и текущая.
			// Чекай https://visjs.github.io/vis-timeline/examples/timeline/editing/updateDataOnEvent.html .
			// Но забил, уходя спать
			//setCustomTime(moment(), 3); // Вертикальная полоса течение времени	

			this.timelineinstance.on('rangechange', (properties) => {

				// Вычисление средней даты между верхней и нижней видимой.
				// Считается неэффективно из-за множественной перегонки через moment,
				// но мне через 6-7 часов уже вставать, спать пора.

				// console.log(Date() + 'new redraw');

				// var end = moment(properties.end);
				// var start = moment(properties.start);
				// var average = moment((end + start)/2);
				// this.params.center = average.format(moment.HTML5_FMT.DATETIME_SECONDS);
				// this.timelineinstance.setCustomTime(average, "custome-type-center")

				// if (properties.byUser) {
				// 	console.log('byUser');
				// 	this.params.play = "false";
				// 	this.$emit('setNewTimelineCenter');
				// }

			});

		},
		created: function () {
		},
		watch: {
			'params.play': function () {

				console.log('params.play');

				if (this.params.play) {
					var timewindow = this.timelineinstance.getWindow();
					var end = moment(timewindow.end);
					var start = moment(timewindow.start);
					var average = moment((end + start)/2).add(1, 'hour');


					this.timelineinstance.moveTo(average, {animation: {duration: 60*60*1000, easingFunction: "linear"}});
				} else {

					var timewindow = this.timelineinstance.getWindow();
					var end = moment(timewindow.end);
					var start = moment(timewindow.start);
					var average = moment((end + start)/2);

					this.timelineinstance.moveTo(average, false);

				}
			},
			'params.downloadeditem': function() {
				debugger;
			}
		}
	}
</script>