// import $ from 'jquery';
import axios from "axios";
import { Controller } from 'stimulus';

export default class extends Controller {

    async publish() {
        const postId = this.element.dataset.postId;
        const status = this.element.checked;

        // console.log(status, postId);
        const promise = await axios.post('/admin/post/publish', {
            postId,
            status
        });

        console.log(`Code : ${promise.status} | Message : ${promise.data}`);
    }
}
