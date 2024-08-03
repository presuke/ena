<script>
import CopyRight from '../CopyRight.vue';
import Header from '../Header.vue';
import Footer from '../Footer.vue';

export default {
	components: {
		CopyRight,
		Header,
		Footer,

	},
	data: () => ({
		dialog:{
			copyright:{
				show: false,
			},
		},
	}),
};
</script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Caveat&family=Reggae+One&display=swap');
</style>
<style lang="scss" scoped>
@import '../../scss/app.scss';
</style>
<style lang="scss" scoped>
@import '../../scss/play.scss';
</style>
<style>
.btn,
a.btn,
button.btn {
  font-size: 1.6rem;
  font-weight: 700;
  line-height: 1.5;
  position: relative;
  display: inline-block;
  padding: 1rem 4rem;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  -webkit-transition: all 0.3s;
  transition: all 0.3s;
  text-align: center;
  vertical-align: middle;
  text-decoration: none;
  letter-spacing: 0.1em;
  color: #212529;
  border-radius: 0.5rem;
}

.btn-border-gradient-wrap {
  display: inline-block;

  padding: 0.2rem;

  border-radius: 0.5rem;
}

.btn-border-gradient-wrap--gold {
  background-image: -webkit-linear-gradient(
    315deg,
    #700843 0%,
    #ff08ce 37%,
    #feb2fe 47%,
    #fad6fa 50%,
    #feb2fe 53%,
    #e108ce 63%,
    #700843 100%
  );
  background-image: linear-gradient(
    135deg,
    #700843 0%,
    #ff08ce 37%,
    #feb2fe 47%,
    #fadafa 50%,
    #feb2fe 53%,
    #e108ce 63%,
    #700843 100%
  );
}

.btn-border-gradient-wrap--gold:hover a.btn {
  text-shadow: 0 0 15px rgba(250, 250, 214, 0.5),
    0 0 15px rgba(250, 250, 214, 0.5), 0 0 15px rgba(250, 250, 214, 0.5),
    0 0 15px rgba(250, 250, 214, 0.5);
}

a.btn-border-gradient {
  font-size: 2rem;
  background: #000;
}

.btn-text-gradient--gold {
  font-family: 'Shrikhand', cursive;

  background: -webkit-gradient(
    linear,
    left bottom,
    left top,
    from(#ffdbff),
    to(#a12264)
  );

  background: -webkit-linear-gradient(bottom, #ffdbff, #a12264);

  background: linear-gradient(to top, #ffdbff, #a12264);
  -webkit-background-clip: text;

  -webkit-text-fill-color: transparent;
}

</style>

<template>
	<header>
		<Header></Header>
	</header>
	<div style="text-align: center; height:calc(100vh - 100px);">
		<div style="margin:auto; position: relative;">

			<img src="image/top2.png" style="max-width:600px; width:100%; height:auto;" />
			<div class="btn-border-gradient-wrap btn-border-gradient-wrap--gold">
				<a 
        href="./room/" 
        style="width:360px;"
        class="btn btn-border-gradient"><span class="btn-text-gradient--gold">Play Game</span></a>
			</div>
			<div class="btn-border-gradient-wrap btn-border-gradient-wrap--gold">
				<a 
        href="https://www.nicovideo.jp/watch/sm43303622" 
        style="width:360px;"
        target="_blank" 
        class="btn btn-border-gradient"><span class="btn-text-gradient--gold">How To</span></a>
			</div>
		</div>
	</div>
	<!--Notifyダイアログ-->
	<v-dialog 
	v-model="this.dialog.copyright.show"
	transition="dialog-top-transition"
	max-width="400"
	>
		<v-card width="320" height="400">
			<v-card-text style="overflow-y: auto;">
				<CopyRight></CopyRight>
			</v-card-text>
			<v-card-actions>
				<v-spacer></v-spacer>
				<v-btn
				color="blue-darken-1"
				variant="text"
				@click="this.dialog.copyright.show = false;"
				>
					閉じる
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
	<footer @click="this.dialog.copyright.show = true;">
		<Footer></Footer>
	</footer>
</template>
