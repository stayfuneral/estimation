window.onload = function () {
    new Vue({
        el: '#app',
        data: {
            estimation: null,
            badEstimationMotives: {
                longSolution: 'Долгое решение проблемы специалистом ИТ',
                slowSpeed: 'Медленная скорость реакция на заявку',
                roughHandling: 'Грубое общение тех. Специалиста',
                problemNotSolved: 'Проблема не решена',
                other: 'Свой вариант'
            },
            whyBad: [],
            comment: '',
            labels: {
                title: 'Пожалуйста, отметьте моменты, которые вас не устроили:',
                textareaPlaceholder: 'Опишите вашу проблему более подробно',
                whyBad: 'Выберите причину вашей оценки:',
                send: 'Отправить',
                response: 'спасибо за оценку!',
                responseComment: ''
            },
            auto: false,
            send: false,
            username: '',
            classObject: {
                'alert alert-success': true,
                'alert alert-danger': false
            },
            ticketId: null,
            result: null
        },
        methods: {
            checkTicket(ticketId) {
                const request = this.postData({
                    type: 'check_estimation_by_ticket',
                    ticketId: ticketId
                });

                request.then(response => {
                    this.result = response.result;
                });


            },
            parseAddressString() {
                const url = new URL(window.location.href);
                this.ticketId = Number(url.searchParams.get('ticket'));
                this.estimation = Number(url.searchParams.get('estimation'));

                if(this.estimation === 1) {
                    this.sendEstimation();
                }
            },
            sendEstimation() {
                const params = {
                    type: 'add_estimation',
                    ticketId: this.ticketId,
                    estimation: this.estimation
                };

                if(this.estimation === 0) {

                    this.classObject['alert alert-success'] = false;
                    this.classObject['alert alert-danger'] = true;
                    this.labels.responseComment = 'Нам очень жаль, что наши специалисты не оправдали ваших ожиданий, мы приложим усилия для исправления ситуации!';



                    let comment = [];

                    if(this.comment.length > 0) {
                        let key = this.whyBad.indexOf('other');
                        delete this.whyBad[key];
                    }

                    this.whyBad.forEach(item => {
                        comment.push(this.badEstimationMotives[item]);

                    })
                    if(this.comment.length > 0) {
                        comment.push(this.comment);
                    }

                    if(comment.length > 1) {

                        for(let i = 0; i < comment.length; i++) {
                            let index = i + 1;
                            comment[i] = index + '. ' + comment[i];
                        }

                    }

                    params.comment = comment;

                }

                const request = this.postData(params);

                request.then(response => {

                    if(response.result === 'success' || response.result === 'duplicate') {
                        this.send = true;
                        this.labels.responseComment = response.comment
                    }

                    console.log(response)

                })
            },
            async postData(params = {}) {
                const request = await fetch('fetch.php', {
                    method: 'post',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(params)
                })

                return await request.json();
            }
        },
        created() {
            this.parseAddressString();
        }
    });
}