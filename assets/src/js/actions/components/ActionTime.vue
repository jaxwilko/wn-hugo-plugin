<template>
    <div :class="`${time ? 'w-14 h-10 -left-[70px] rounded-2xl' : 'size-10 -left-[62px] rounded-full'} flex justify-center items-center absolute border-2 border-blue-800 bg-blue-800 text-white text-xs font-bold shadow-lg shadow-blue-700/60`">
        {{time}}
    </div>
</template>
<script>
export default {
    name: 'ActionTime',
    props: ['time'],
};
</script>
