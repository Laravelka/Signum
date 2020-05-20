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
				<v-btn @click="reloadPage" color="primary">Обновить</v-btn>
			</v-col>
		</v-row>
	</v-container>
	<v-container v-else>
		<v-row align="center" justify="center">
			<v-col xs="12" sm="12" md="10" lg="8" xl="6">
				<v-card>
					<v-list
						subheader
						two-line
						flat
					>
						<v-subheader>Основное</v-subheader>
						<v-list-item-group
							v-model="settings"
							multiple
						>
							<v-list-item>
								<template>
									<v-list-item-action>
										<v-checkbox
											v-model="isDark"
											color="primary"
										></v-checkbox>
									</v-list-item-action>
									<v-list-item-content>
										<v-list-item-title>Темная тема</v-list-item-title>
										<v-list-item-subtitle>Переключение цветовой темы на темную</v-list-item-subtitle>
									</v-list-item-content>
								</template>
							</v-list-item>
						</v-list-item-group>
					</v-list>
				</v-card>
			</v-col>
		</v-row>
	</v-container>
</template>

<script>
	import {
		setCookie,
		getCookie,
		deleteCookie
	} from '@/js/helpers/cookies';

	export default {
		data: () => ({
			error: false,
			isDark: (
				getCookie('darkTheme') == 1 ? true : false
			),
			loading: false,
			settings: [],
			title: 'Настройки',
		}),
		watch: {
			isDark: function(newDark) {
				this.toggleTheme(newDark);
			}
		},
		created() {
			var app = this;

			app.$root.$emit('active-panel', {
				id: 2,
				title: this.title
			});
		},
		methods: {
			toggleTheme(isDark) {
				if (isDark == true) {
					this.$root.$emit('toggle-theme', {
						dark: true
					});
					setCookie('darkTheme', 1, (99999 * 99999));
				} else {
					this.$root.$emit('toggle-theme', {
						dark: false
					});
					deleteCookie('darkTheme');
				}
			},
			reloadPage() {
				location.reload();
			}
		}
	}
</script>