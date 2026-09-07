<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Server Status</title>

        <style>
            body { margin: 0; font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.5; background-color: #f3f4f6; }
            h1 { margin: 0 0 1.5rem; font-size: 1.5rem; line-height: 2rem; font-weight: 700; color: #111827; }
            main { max-width: 80rem; margin: 0 auto; padding: 2.5rem 1rem; }
        </style>

        @livewireStyles
    </head>

    <body>
        <main>
            <h1>Server Status</h1>

            <livewire:server-status />
        </main>

        @livewireScripts
    </body>
</html>
