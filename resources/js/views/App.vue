<template>
	<v-app id="inspire">
		<v-navigation-drawer
			app
			v-model="drawer"
			v-if="$auth.ready() && $auth.check()"
		>
			<v-list-item>
				<v-list-item-content>
					<v-list-item-title class="title">
						Signum.video
					</v-list-item-title>
					<v-list-item-subtitle>
						Видеонаблюдение в вашем доме
					</v-list-item-subtitle>
				</v-list-item-content>
			</v-list-item>
			<v-divider></v-divider>
			<v-list dense>
				<v-list-item-group v-model="activeBtn">
					<v-list-item v-for="item in drawerLinks[$auth.user().roles]" v-bind:key="item.icon" @click.prevent="clickLink(item)" link>
						<v-list-item-action>
							<v-icon>{{ item.icon }}</v-icon>
						</v-list-item-action>
						<v-list-item-content>
							<v-list-item-title>{{ item.title }}</v-list-item-title>
						</v-list-item-content>
					</v-list-item>
				</v-list-item-group>
			</v-list>
			<div class="pa-2 justify-center align-center d-flex mt-3">
				<v-btn color="indigo" dark rounded block v-if="mobileDetect.os() == 'AndroidOS'"  href="/Signum.apk" download>
					<v-icon>mdi-download</v-icon> Скачать
				</v-btn>
				<v-btn color="indigo" dark rounded block v-else-if="mobileDetect.os() == 'iOS'" @click="installApp()">
					<v-icon>mdi-download</v-icon> Скачать
				</v-btn>
				<v-btn color="indigo" dark rounded block v-else @click="installApp()">
					<v-icon>mdi-download</v-icon> Скачать
				</v-btn>
			</div>
		</v-navigation-drawer>
		<div v-if="$router.currentRoute.path !== '/' && $router.currentRoute.path !== '/admin'">
			<v-app-bar v-if="$auth.check() && !hide.appBar" app dark>
				<v-btn icon @click="goBack">
					<v-icon>mdi-arrow-left</v-icon>
				</v-btn>
				<v-toolbar-title>{{ title }}</v-toolbar-title>
				<v-spacer></v-spacer>
				<v-btn icon @click="onRefresh">
					<v-icon>mdi-refresh</v-icon>
				</v-btn>
			</v-app-bar>
		</div>
		<div v-else>
			<v-app-bar v-if="$auth.check() && !hide.appBar" app dark>
				<v-app-bar-nav-icon @click.stop="drawer = !drawer" />
				<v-toolbar-title>{{ title }}</v-toolbar-title>
				<v-spacer></v-spacer>
				<v-btn icon @click="onRefresh">
					<v-icon>mdi-refresh</v-icon>
				</v-btn>
			</v-app-bar>
		</div>
		<v-content>
			<router-view></router-view>
		</v-content>
		<v-bottom-navigation v-if="$auth.check() && !hide.bottomNavigation && $auth.user().roles != 'admin'"  :value="activeBtn" grow hide-on-scroll fixed app>
			<v-btn :to="{name: 'home'}">
				<v-icon>mdi-home</v-icon>
			</v-btn>
			<v-btn :to="{name: 'home'}">
				<v-icon>mdi-bell</v-icon>
			</v-btn>
			<v-btn :to="{name: 'settings'}">
				<v-icon>mdi-settings</v-icon>
			</v-btn>
		</v-bottom-navigation>
	</v-app>
</template>

<script>
	import mobileDetect from 'mobile-detect';
	
	export default {
		props: {
			source: String,
		},
		data: () => ({
			hide: {
				appBar: false,
				bottomNavigation: false
			},
			title: 'Signum.video',
			drawer: null,
			activeBtn: null,
			drawerLinks: {
				user: [
					{to: {name: 'home'}, title: 'Главная', icon: 'mdi-home'},
					{to: {name: 'home'}, title: 'Уведомления', icon: 'mdi-bell'},
					{to: {name: 'settings'}, title: 'Настройки', icon: 'mdi-settings'},
					{title: 'Выход', icon: 'mdi-exit-to-app', isExit: true}
				],
				admin: [
					{to: {name: 'adminHome'}, title: 'Главная', icon: 'mdi-home'},
					{to: {name: 'adminNotifications'}, title: 'Уведомления', icon: 'mdi-bell'},
					{to: {name: 'adminSettings'}, title: 'Настройки', icon: 'mdi-settings'},
					{title: 'Выход', icon: 'mdi-exit-to-app', isExit: true}
				]
			},
			mobileDetect: null,
			deferredPrompt: null,
		}),
		watch: {
			'$route': function(newRoute) {
				if (newRoute.meta.buttonId)
				{
					this.activeBtn = newRoute.meta.buttonId;
				}
			}
		},
		methods: {
			installApp() {
				console.log('установка PWA');
				
				this.deferredPrompt.prompt();
				// Wait for the user to respond to the prompt
				this.deferredPrompt.userChoice.then((choiceResult) => {
					if (choiceResult.outcome === 'accepted') {
						console.log('User accepted the install prompt');
					} else {
						console.log('User dismissed the install prompt');
					}
				});
			},
			clickLink(data) {
				if (data.isExit)
				{
					this.$auth.logout();
				}
				else
				{
					if (this.$router.currentRoute.name != data.to.name)
						this.$router.push(data.to);
				}
			},
			goBack() {
				window.history.length > 1 ? this.$router.go(-1) : this.$router.push('/')
			},
			onRefresh() {
				this.$root.$emit('clickedRefreshButton', true);
			}
		},
		created() {
			this.mobileDetect = new mobileDetect(navigator.userAgent);
			
			if (this.mobileDetect.os() == 'iOS' || this.mobileDetect.os() == null)
			{
				console.log('is pwa');
				
				window.addEventListener('beforeinstallprompt', (e) => {
					// Prevent the mini-infobar from appearing on mobile
					e.preventDefault();
					// Stash the event so it can be triggered later.
					this.deferredPrompt = e;
					// Update UI notify the user they can install the PWA
					console.log('Установи ебанное приложение', e);
				});
			}
		},
		mounted() {
			var app = this;
			
			app.$root.$on('hide-object', function(data) {
				data.forEach((item) => {
					app.hide[item] = true;
				});
			});
			
			app.$root.$on('active-panel', function(data) {
				app.title = data.title;
				app.activeBtn = data.id; 
				document.title = data.title;
			});
		}
	}
</script>
