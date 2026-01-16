<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alpine Test</title>

    @vite(['resources/js/app.js'])
</head>
<body class="p-6">

    <h1>Alpine Minimal Test</h1>

    <div
        x-data="{
            message: 'Hello Alpine',
            count: 0,
            items: ['One', 'Two', 'Three'],

            increment() {
                this.count++
            }
        }"
        class="mt-4 border p-4"
    >
        <p x-text="message"></p>
        <p>Count: <span x-text="count"></span></p>

        <button @click="increment">
            Increment
        </button>

        <ul class="mt-2">
            <template x-for="item in items" :key="item">
                <li x-text="item"></li>
            </template>
        </ul>
    </div>

</body>
</html>
