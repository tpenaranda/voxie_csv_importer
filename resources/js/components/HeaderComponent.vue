<template>
    <div class="header">
        <b-container class="text-center" fluid>
            <b-row align-h="center">
                <b-col align-self="center" cols="12" sm="5"><img src="https://www.voxie.com/wp-content/uploads/elementor/thumbs/VOXIE-black-o1ol2ora2qld6vodwmsrb0qd5dj7ere5midef05xk8.png" width="240" height="120"></b-col>
                <b-col align-self="center" cols="auto"></b-col>
                <b-col align-self="center" cols="12" sm="5"><p class="title">CSV File Parser</p></b-col>
            </b-row>
        </b-container>

        <b-progress :max="3" class="step-bar" fluid>
          <b-progress-bar :value="$root.step" :label="`Step ${$root.step} / 3`"></b-progress-bar>
        </b-progress>
    </div>
</template>

<script>
    export default {
        name: 'HeaderComponent',
    }
</script>

<style lang="scss" scoped>
    .title {
        border-bottom: 1px solid #14abe0;
        border-top: 1px solid #14abe0;
        font-size: 1.25em;
        font-weight: bold;
        margin: auto;
    }
    .progress.step-bar {
        width: 85%;
        height: 2rem !important;
        font-size: 1em;
        margin: 1em auto 0 auto;
    }
</style>
