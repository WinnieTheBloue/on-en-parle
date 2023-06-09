<template>
    <AppLayout
        :title="room ? `On en parle | Questions ${room.name}` : 'On en parle'"
    >
        <div v-if="room" class="chat-wrapper">
            <div class="chat-transmission">
                <div class="transmission-card-container">
                    <div class="on-air card" v-if="room.on_air">
                        <transmission-card :room="room" />
                    </div>
                    <div class="card" v-else>
                        <transmission-card :room="room" />
                    </div>
                </div>
            </div>

            <Player
                v-if="room.audio_file && !room.on_air"
                :room="room"
                :status="playerStatus"
            />
            <div class="chat-container">
                <message-container :messages="messages" />
                <input-message :room="room.id" v-on:messagesent="getMessages" />
            </div>
        </div>
    </AppLayout>
</template>

<script>
/**
 * Component: AppLayout
 * Description: The layout component for the application.
 *
 * Component: MessageContainer
 * Description: The container component for displaying messages in a chat room.
 *
 * Component: InputMessage
 * Description: The component for inputting a message in a chat room.
 *
 * Component: ChatRoomSelection
 * Description: The component for selecting a chat room.
 *
 * Component: TransmissionCard
 * Description: The component for displaying transmission cards.
 *
 * Component: Player
 * Description: The component for controlling audio player.
 */
import AppLayout from "@/Layouts/AppLayoutUser.vue";
import MessageContainer from "@/Pages/Chat/messageContainer.vue";
import InputMessage from "@/Pages/Chat/inputMessage.vue";
import ChatRoomSelection from "@/Pages/Chat/chatRoomSelection.vue";
import TransmissionCard from "@/Pages/MyComponents/transmission-card.vue";
import Player from "@/Pages/Chat/player.vue";
import axios from "axios";

export default {
    components: {
        AppLayout,
        MessageContainer,
        InputMessage,
        ChatRoomSelection,
        TransmissionCard,
        Player,
    },
    data: function () {
        return {
            messages: [],
            room: null,
            playerStatus: false,
        };
    },
    methods: {
        /**
         * Retrieves the room data from the server.
         */
        getRoom() {
            axios.get("/index/room").then((response) => {
                this.room = response.data;
            });
        },

        /**
         * Retrieves the messages for the current room from the server.
         */
        getMessages() {
            axios
                .get("/chat/room/" + this.room.id + "/messages")
                .then((response) => {
                    this.messages = response.data;
                })
                .catch((error) => {
                    console.log(error);
                });
        },

        /**
         * Toggles the play/pause state of the audio player.
         */
        togglePlayPause() {
            const player = document.querySelector("audio");
            const playIcon = document.querySelector(".play-icon");
            const playIconSecond = document.querySelector(".play-icon-second");
            if (player.paused) {
                player.play();
                this.playerStatus = true;
                playIcon.innerHTML = playIconSecond.innerHTML = "pause";
            } else {
                player.pause();
                this.playerStatus = false;
                playIcon.innerHTML = playIconSecond.innerHTML = "play_circle";
            }
        },

        /**
         * Handles the playback of the transmission.
         */
        playTransmission() {
            const player = document.querySelector("audio");
            const progressBar = document.querySelector(".player-progress-bar");
            const playIconSecond = document.querySelector(".play-icon-second");
            const back = document.querySelector(".back");
            const forward = document.querySelector(".forward");

            player.addEventListener("error", (event) => {
                this.togglePlayPause();
            });

            this.togglePlayPause();
            player.addEventListener("timeupdate", function () {
                progressBar.max = player.duration;
                progressBar.value = player.currentTime;
            });
            progressBar.addEventListener("change", function () {
                player.currentTime = progressBar.value;
            });

            playIconSecond.addEventListener("click", () => {
                this.togglePlayPause();
            });

            back.addEventListener("click", () => {
                player.currentTime -= 10;
            });
            forward.addEventListener("click", () => {
                player.currentTime += 10;
            });
        },
    },
    watch: {
        /**
         * Watches the 'room' property for changes and updates the messages and event listeners accordingly.
         */
        room() {
            this.getMessages();

            const likesChannel = Echo.channel("like." + this.room.id);
            likesChannel.listen(".like.new", (e) => {
                this.getMessages();
            });

            const chatChannel = Echo.channel("chat." + this.room.id);
            chatChannel.listen(".message.new", (e) => {
                this.getMessages();
            });
        },
    },
    created() {
        /**
         * Fetches the initial room data and sets up the live status event listener.
         */
        this.getRoom();

        const liveChannel = Echo.channel("live.status");
        liveChannel.listen(".live.status.new", (e) => {
            this.getRoom();
        });
    },
};
</script>
