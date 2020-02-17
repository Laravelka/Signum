import Vue from 'vue';
import VueRouter from 'vue-router';

import Home from '@/js/components/Home';
import Monit from '@/js/components/Monit';
import Login from '@/js/components/Login';
import Notify from '@/js/components/Notify';
import Monitor from '@/js/components/Monitor';
import Register from '@/js/components/Register';
import Settings from '@/js/components/Settings';
import NotFound from '@/js/components/NotFound';

Vue.use(VueRouter);

const router = new VueRouter({
	mode: 'history',
	routes: [
		{
			path: '/',
			name: 'home',
			component: Home,
			meta: {
				auth: true,
				buttonId: 0,
			}
		},
		{
			path: '/login',
			name: 'login',
			component: Login,
			meta: {
				auth: false
			}
		},
		{
			path: '/register',
			name: 'register',
			component: Register,
			meta: {
				auth: false
			}
		},
		{
			path: '/monitor/:id',
			name: 'monitor',
			component: Monitor,
			meta: {
				auth: true
			}
		},
		{
			path: '/m/:id',
			name: 'm',
			component: Monit,
			meta: {
				auth: true
			}
		},
		{
			path: '/settings',
			name: 'settings',
			component: Settings,
			meta: {
				auth: true,
				buttonId: 1,
			}
		},
		{
			path: '/notify',
			name: 'notify',
			component: Notify,
			meta: {
				auth: true,
				buttonId: 2,
			}
		},
		{
			path: '*',
			component: NotFound
		}
	]
});

export default router;