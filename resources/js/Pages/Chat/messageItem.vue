<template>
    <div class="left">
        <h2 v-if="message.text[0]" class="text-message">Question</h2>
        <h2 v-else class="transcription-message">Transcription</h2>
        <p>{{ message.content }}</p>
    </div>
    <div class="right">
        <p>{{ formatRelativeTime(message.created_at) }}</p>
        <button
            :id="'like-' + message.id"
            @click="like()"
            :class="{ liked: isLiked() }"
        >
            <span class="material-symbols-outlined">favorite</span>
        </button>
        <p>{{ message.nb_likes }}</p>
        <p></p>
    </div>
</template>

<script>
import axios from "axios";
import { formatRelativeTime } from "../../utils/timeFormat.js";
import { setItem, getItem, unsetItem } from "../../utils/local.js";

export default {
    props: ["message"],
    data: function () {
        return {
            localStorage: "1234",
        };
    },
    methods: {
        /**
         * Sets an item in local storage.
         * @param {string} key - The key of the item.
         * @param {any} value - The value of the item.
         */
        setItem,
        /**
         * Retrieves an item from local storage.
         * @param {string} key - The key of the item.
         * @returns {any} The value of the item.
         */
        getItem,
        /**
         * Removes an item from local storage.
         * @param {string} key - The key of the item.
         */
        unsetItem,
        /**
         * Formats a relative time based on the current time.
         * @param {Date} time - The time to format.
         * @returns {string} The formatted relative time.
         */
        formatRelativeTime,
        /**
         * Handles the like action on the message.
         */
        like() {
            if (window.location.href.includes("/admin/programs/program/")) {
                return;
            } else {
                if (this.isLiked()) {
                    axios
                        .post("/chat/unlike/" + this.message.id, {})
                        .then((response) => {
                            if (response.status == 200) {
                                unsetItem(this.message.id);
                                document
                                    .getElementById("like-" + this.message.id)
                                    .classList.remove("liked");
                            }
                        });
                    return;
                } else {
                    axios
                        .post("/chat/like/" + this.message.id, {})
                        .then((response) => {
                            if (response.status == 200) {
                                setItem(this.message.id);
                                document
                                    .getElementById("like-" + this.message.id)
                                    .classList.add("liked");
                            }
                        })
                        .catch((error) => {
                            console.log(error);
                        });
                }
            }
        },
        /**
         * Checks if the message is liked.
         * @returns {boolean} True if the message is liked, false otherwise.
         */
        isLiked() {
            if (getItem(this.message.id)) {
                return true;
            }
        },
    },
};
</script>
