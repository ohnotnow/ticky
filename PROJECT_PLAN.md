This is an initial proof of concept for a laravel/livewire app which lets an IT support person give a support ticket
to an LLM, which is then also given a triage prompt and the organisation structure and staff/skills and is asked who
the ticket should be assigned to.

I have sketched out the basic core models, created a basic PoC config file in config/ticky.php.

The front-end will be full-page livewire components (we already have the base layout in resources/views/components/layouts/app.blade.php).  We will be using the FluxUI component library for the frond-end components.
