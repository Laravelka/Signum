import Vue from 'vue';
import axios from 'axios';
import Vuetify from 'vuetify';
import VueAxios from 'vue-axios'
import VueRouter from 'vue-router';
import DatetimePicker from 'vuetify-datetime-picker'

import '@/css/style.css';
import App from '@/js/views/App';
import Routes from '@/js/routes.js';
import {setCookie, getCookie} from '@/js/helpers/cookies';

import timeline from '@/js/components/Timeline';
import 'vis-timeline/dist/vis-timeline-graph2d.min.css';

Vue.use(DatetimePicker);
Vue.use(VueAxios, axios);
Vue.component('timeline', timeline);

axios.defaults.baseURL = 'https://ex-coin.space/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Authorization'] = 'Bearer ' + getCookie('default_auth_token');

window.axios = axios;
Vue.use(Vuetify);
Vue.router = Routes;

Vue.use(require('@websanova/vue-auth'), {
	rolesVar: 'roles',
	fecthData: { enabled: true },
	refreshData: { enabled: false },
	logoutData: { redirect: '/login' },
	auth: require('@websanova/vue-auth/drivers/auth/bearer.js'),
	http: require('@websanova/vue-auth/drivers/http/axios.1.x.js'),
	router: require('@websanova/vue-auth/drivers/router/vue-router.2.x.js'),
});

Vue.router.beforeEach((to, from, next) => {
	if (to.matched.some(record => record.meta.auth))
	{
		if (!Vue.auth.check())
		{
			if (to.name !== 'login')
			{
				next({
					name: 'login'
				});
			}
		}
		else
		{
			const user = Vue.auth.user();
			
			if (user.roles == 'admin' && to.name == 'home')
			{
				next({name: 'adminHome'});
			}
			else
			{
				next();
			}
		}
	}
	else
	{
		sessionStorage.removeItem('getMonitors');
		
		next();
	}
});

const app = new Vue({
	el: '#app',
	router: Vue.router,
	render: h => h(App),
	vuetify : new Vuetify({
		icons: {
			iconfont: 'mdi',
		},
		theme: {
			dark: (getCookie('darkTheme') == 1 ? true : false),
			themes: {
				dark: {
					primary: '#303F9F',
					accent: '#FF4081',
					secondary: '#ffe18d',
					success: '#4CAF50',
					info: '#2196F3',
					warning: '#FB8C00',
					error: '#FF5252'
				},
				light: {
					primary: '#5C6BC0',
					accent: '#e91e63',
					secondary: '#30b1dc',
					success: '#4CAF50',
					info: '#2196F3',
					warning: '#FB8C00',
					error: '#FF5252'
				}
			}
		}
	}),
	mounted() {
		var app = this;
		
		app.$root.$on('toggle-theme', function(data) {
			app.$vuetify.theme.dark = data.dark;
		});
	}
});

export default app;