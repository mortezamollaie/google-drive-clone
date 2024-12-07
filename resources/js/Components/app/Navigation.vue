<template>
    <nav class="min-w-[200px]">
        <div class="h-[80px] px-3 flex items-center gap-3">
            <Link href="/">
                <ApplicationLogo
                    class="black h-9 w-auto fill-current tex-gray-800"
                />
            </Link>
            LaraStorage
        </div>
        <div class="px-3">
            <CreateNewDropdown />
            <div class="py-3">
                <NavLink href="/">My Files</NavLink>
                <NavLink href="/">Shared with me</NavLink>
                <NavLink href="/">Shared By me</NavLink>
                <NavLink href="/">Trash</NavLink>
            </div>
        </div>
    </nav>
</template>

<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import CreateNewDropdown from "@/Components/app/CreateNewDropdown.vue";
import {Link} from '@inertiajs/vue3'
import NavLink from "@/Components/NavLink.vue";
</script>

<style scoped>

</style>
