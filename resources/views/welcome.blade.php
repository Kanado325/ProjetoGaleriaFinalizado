
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ateliê Sonhador</title>

    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text x=%2250%25%22 y=%22.9em%22 font-size=%2290%22 text-anchor=%22middle%22 font-family=%22Arial,sans-serif%22 fill=%22%23a78bfa%22>&#x269B;</text></svg>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    class="bg-[#f7f2ff] text-[#02000f] min-h-screen flex flex-col"
    x-data="galleryApp()"
    x-init="fetchPhotos()"
>
    <!-- Cabeçalho -->
    <header class="flex flex-col gap-4 border-b border-gray-200 px-4 py-4 md:flex-row md:items-center md:justify-between md:gap-0 md:px-6">

        <!-- Usuário -->
        <div class="flex w-full items-center justify-center gap-3 md:w-1/3 md:justify-start">
            <template x-if="user">
                <div class="flex items-center gap-3">
                    <img
                        :src="user.profile_img
                            ? '/storage/' + user.profile_img
                            : '/images/default-img.png'"
                        alt="Foto de perfil"
                        class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover"
                    >

                    <span
                        class="max-w-[130px] truncate font-semibold"
                        x-text="'@' + (user.username || user.name)"
                    ></span>

                    <button
                        type="button"
                        @click="openProfileModal = true; fetchStats()"
                        :disabled="user.role !== 'fotografo'"
                        class="whitespace-nowrap rounded bg-[#02000f] px-3 py-1 text-sm font-semibold text-white transition hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Ver Perfil
                    </button>
                </div>
            </template>
        </div>

        <!-- Nome da aplicação -->
        <div class="order-first w-full text-center md:order-none md:w-1/3">
            <h1 class="text-xl font-bold tracking-tight text-[#02000f] sm:text-2xl">
                <span class="inline-block text-3xl text-violet-400 transition-transform duration-300 hover:rotate-12">⚛</span>
                Ateliê <span class="text-violet-400">Sonhador</span>
            </h1>
        </div>

        <!-- Login / Logout -->
        <div class="flex w-full justify-center md:w-1/3 md:justify-end">
            <template x-if="!user">
                <button
                    type="button"
                    @click="showLoginModal = true; authMode = 'login'"
                    class="rounded bg-[#02000f] px-5 py-2 font-semibold text-white transition hover:bg-violet-700"
                >
                    Login
                </button>
            </template>

            <template x-if="user">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="rounded bg-[#02000f] px-5 py-2 font-semibold text-white transition hover:bg-red-600"
                    >
                        Sair
                    </button>
                </form>
            </template>
        </div>
    </header>

    <!-- Perfil do fotógrafo -->
    <div
        x-show="openProfileModal"
        x-cloak
        x-transition
        @keydown.escape.window="openProfileModal = false"
        @click.self="openProfileModal = false"
        class="fixed inset-0 z-40 bg-[#02000f]/30"
    >
        <aside class="fixed inset-y-0 left-0 w-full max-w-xs overflow-y-auto border-r border-gray-300 bg-[#fcf7f0] p-6 shadow-2xl">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-bold">Perfil do Fotógrafo</h2>

                <button
                    type="button"
                    @click="openProfileModal = false"
                    class="text-2xl font-bold transition hover:text-violet-600"
                    aria-label="Fechar perfil"
                >
                    &times;
                </button>
            </div>

            <template x-if="statsLoading">
                <p class="text-sm text-gray-500">Carregando estatísticas...</p>
            </template>

            <template x-if="!statsLoading">
                <div class="space-y-4">
                    <p class="text-lg">
                        <span class="font-bold" x-text="stats.publications"></span>
                        Publicações
                    </p>

                    <p class="border-y border-[#02000f] py-2 text-lg">
                        <span class="font-bold" x-text="stats.likes"></span>
                        Curtidas recebidas
                    </p>

                    <template x-if="statsError">
                        <p class="text-sm text-red-600" x-text="statsError"></p>
                    </template>
                </div>
            </template>
        </aside>
    </div>

    <!-- Conteúdo principal -->
    <main class="mx-auto w-full max-w-6xl flex-grow px-4 py-8">

        <!-- Botão de publicação -->
        <template x-if="user && user.role === 'fotografo'">
            <div class="mb-6 flex justify-start">
                <button
                    type="button"
                    @click="showUploadModal = true"
                    class="flex items-center gap-2 rounded-lg bg-[#02000f] px-5 py-3 text-sm font-semibold text-white transition hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500"
                >
                    <span class="text-xl">+</span>
                    Upload de foto
                </button>
            </div>
        </template>

        <!-- Modal de publicação -->
        <div
            x-show="showUploadModal"
            x-cloak
            x-transition.opacity
            @keydown.escape.window="showUploadModal = false"
            @click.self="showUploadModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#02000f]/60 p-4 backdrop-blur-sm"
        >
            <div
                x-transition
                class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-[#02000f] bg-[#fcf7f0] p-6 shadow-xl"
            >
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-bold">Nova publicação</h2>

                    <button
                        type="button"
                        @click="showUploadModal = false"
                        class="text-2xl font-bold transition hover:text-violet-600"
                        aria-label="Fechar modal"
                    >
                        &times;
                    </button>
                </div>

                <form @submit.prevent="uploadPhoto()" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label for="photo_title" class="mb-1 block text-sm font-semibold">
                            Título da foto
                        </label>

                        <input
                            id="photo_title"
                            type="text"
                            x-model="photoForm.title"
                            placeholder="Ex: Nuvens sobre o mar"
                            maxlength="255"
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"
                        >
                    </div>

                    <div>
                        <label for="photo_location" class="mb-1 block text-sm font-semibold">
                            Localização
                        </label>

                        <input
                            id="photo_location"
                            type="text"
                            x-model="photoForm.location"
                            placeholder="Ex: Xique-Xique, BA"
                            maxlength="255"
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"
                        >
                    </div>

                    <div>
                        <label for="photo_image" class="mb-1 block text-sm font-semibold">
                            Imagem
                        </label>

                        <input
                            id="photo_image"
                            type="file"
                            accept="image/*"
                            required
                            @change="photoForm.image = $event.target.files[0] || null"
                            class="w-full rounded-lg border border-gray-300 bg-white p-2 text-sm"
                        >
                    </div>

                    <template x-if="uploadError">
                        <p class="text-sm text-red-600" x-text="uploadError"></p>
                    </template>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="closeUploadModal()"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            :disabled="uploading"
                            class="rounded-lg bg-[#02000f] px-5 py-2 text-sm font-semibold text-white transition hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span x-text="uploading ? 'Publicando...' : 'Publicar foto'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Busca -->
        <div class="relative mx-auto mb-8 w-full max-w-xl">
            <input
                type="text"
                x-model="searchQuery"
                @input.debounce.300ms="fetchPhotos()"
                placeholder="Pesquisar fotógrafo..."
                aria-label="Pesquisar fotógrafo"
                class="w-full rounded-lg border border-gray-300 bg-white py-3 pl-4 pr-12 text-sm text-gray-800 placeholder:text-gray-400 transition focus:border-[#02000f] focus:outline-none focus:ring-2 focus:ring-[#02000f]/10"
            >

            <img
                src="/icons/lupa.svg"
                alt=""
                class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 opacity-50"
            >
        </div>

        <!-- Erros e estado de carregamento -->
        <template x-if="searchError">
            <p class="mb-6 text-center font-semibold text-red-600" x-text="searchError"></p>
        </template>

        <template x-if="loadingPhotos">
            <p class="mb-6 text-center text-sm text-gray-500">Carregando publicações...</p>
        </template>

        <!-- Galeria -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
            <template x-for="photo in photos" :key="photo.id">
                <article class="group relative overflow-hidden rounded border border-gray-300 shadow-sm transition-all duration-300 ease-out hover:scale-[1.02] hover:shadow-lg">

                    <!-- Imagem -->
                    <div class="relative overflow-hidden">
                        <img
                            :src="photo.image_url"
                            :alt="photo.title || 'Fotografia publicada'"
                            loading="lazy"
                            class="h-64 w-full bg-gray-100 object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                        >

                        <!-- Informações no hover -->
                        <div class="pointer-events-none absolute inset-0 flex flex-col justify-end bg-[#02000f]/75 p-4 text-white opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                            <p
                                class="text-sm font-semibold"
                                x-text="'Foto tirada por: ' + (photo.photographer || 'Desconhecido')"
                            ></p>

                            <p
                                class="text-sm"
                                x-text="'Local: ' + (photo.location || 'Não informado')"
                            ></p>
                        </div>
                    </div>

                    <!-- Título e curtidas -->
                    <div class="flex items-center justify-between gap-3 border-t border-gray-100 bg-[#fcf7f0] p-4">
                        <span
                            class="min-w-0 truncate font-semibold text-gray-800"
                            :title="photo.title"
                            x-text="photo.title"
                        ></span>

                        <div class="flex shrink-0 items-center gap-1.5">
                            <button
                                type="button"
                                @click="toggleLike(photo)"
                                :disabled="likingIds.includes(photo.id)"
                                :aria-label="photo.is_liked ? 'Remover curtida' : 'Curtir foto'"
                                class="transition-transform hover:scale-110 focus:outline-none disabled:opacity-50"
                            >
                                <svg
                                    class="h-6 w-6 transition-colors"
                                    :class="photo.is_liked ? 'fill-current text-violet-500' : 'fill-none text-[#02000f]'"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                    />
                                </svg>
                            </button>

                            <span
                                class="font-bold text-sm"
                                x-text="photo.likes_count ?? 0"
                            ></span>
                        </div>
                    </div>
                </article>
            </template>
        </div>

        <template x-if="!loadingPhotos && photos.length === 0 && !searchError">
            <p class="py-10 text-center text-gray-500">
                Nenhuma publicação encontrada.
            </p>
        </template>
    </main>

    <!-- Modal de autenticação -->
    <div
        x-show="showLoginModal"
        x-cloak
        x-transition.opacity
        @keydown.escape.window="showLoginModal = false"
        @click.self="showLoginModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-[#02000f]/60 p-4 backdrop-blur-sm"
    >
        <div class="relative my-auto max-h-[90vh] w-full max-w-md overflow-y-auto rounded-lg border border-[#02000f] bg-[#fcf7f0] p-6 shadow-xl">

            <button
                type="button"
                @click="showLoginModal = false"
                class="absolute right-4 top-4 text-xl font-bold hover:text-violet-600"
                aria-label="Fechar modal"
            >
                &times;
            </button>

            <!-- Abas -->
            <div class="mb-6 flex border-b border-gray-300 pr-8">
                <button
                    type="button"
                    @click="authMode = 'login'"
                    :class="authMode === 'login' ? 'border-b-2 border-[#02000f] font-bold text-[#02000f]' : 'text-gray-500'"
                    class="flex-1 py-2 text-center text-lg"
                >
                    Login
                </button>

                <button
                    type="button"
                    @click="authMode = 'register'"
                    :class="authMode === 'register' ? 'border-b-2 border-[#02000f] font-bold text-[#02000f]' : 'text-gray-500'"
                    class="flex-1 py-2 text-center text-lg"
                >
                    Cadastre-se
                </button>
            </div>

            <!-- Login -->
            <form
                x-show="authMode === 'login'"
                method="POST"
                action="{{ route('login') }}"
                class="space-y-4"
            >
                @csrf

                <h2 class="text-xl font-bold text-[#02000f]">Entrar na conta</h2>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">
                        E-mail
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Digite seu e-mail"
                        autocomplete="email"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none transition focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-500/10"
                    >

                    @if ($errors->has('email'))
                        <span class="mt-1 block text-xs text-red-600">
                            {{ $errors->first('email') }}
                        </span>
                    @endif
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700">
                        Senha
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Digite sua senha"
                        autocomplete="current-password"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none transition focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-500/10"
                    >

                    @if ($errors->has('password'))
                        <span class="mt-1 block text-xs text-red-600">
                            {{ $errors->first('password') }}
                        </span>
                    @endif
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button
                        type="button"
                        @click="showLoginModal = false"
                        class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-100"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="rounded-lg bg-[#02000f] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700"
                    >
                        Entrar
                    </button>
                </div>
            </form>

            <!-- Cadastro -->
            <form
                x-show="authMode === 'register'"
                method="POST"
                action="{{ route('register') }}"
                enctype="multipart/form-data"
                class="space-y-3"
            >
                @csrf

                <h2 class="text-xl font-bold text-[#02000f]">Criar conta</h2>

                <div>
                    <label for="register_name" class="mb-1 block text-sm font-medium text-gray-700">
                        Nome completo
                    </label>

                    <input
                        id="register_name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="João Silva"
                        autocomplete="name"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none transition focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-500/10"
                    >

                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <label for="register_username" class="mb-1 block text-sm font-medium text-gray-700">
                        Nome de usuário
                    </label>

                    <input
                        id="register_username"
                        type="text"
                        name="username"
                        value="{{ old('username') }}"
                        placeholder="joaosilva"
                        autocomplete="username"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none transition focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-500/10"
                    >

                    <x-input-error :messages="$errors->get('username')" class="mt-1" />
                </div>

                <div>
                    <label for="register_email" class="mb-1 block text-sm font-medium text-gray-700">
                        E-mail
                    </label>

                    <input
                        id="register_email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="exemplo@email.com"
                        autocomplete="email"
                        oninput="this.value = this.value.toLowerCase()"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none transition focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-500/10"
                    >

                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <label for="register_role" class="mb-1 block text-sm font-medium text-gray-700">
                        Tipo de perfil
                    </label>

                    <select
                        id="register_role"
                        name="role"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10"
                    >
                        <option value="usuario" @selected(old('role', 'usuario') === 'usuario')>
                            Usuário comum
                        </option>
                        <option value="fotografo" @selected(old('role') === 'fotografo')>
                            Fotógrafo
                        </option>
                    </select>

                    <x-input-error :messages="$errors->get('role')" class="mt-1" />
                </div>

                <div>
                    <label for="register_password" class="mb-1 block text-sm font-medium text-gray-700">
                        Senha
                    </label>

                    <input
                        id="register_password"
                        type="password"
                        name="password"
                        placeholder="Mínimo de 6 caracteres"
                        minlength="6"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none transition focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-500/10"
                    >

                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div>
                    <label for="register_password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">
                        Confirmar senha
                    </label>

                    <input
                        id="register_password_confirmation"
                        type="password"
                        name="password_confirmation"
                        placeholder="Repita sua senha"
                        minlength="6"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none transition focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-500/10"
                    >
                </div>

                <div>
                    <label for="profile_img" class="mb-1 block text-sm font-medium text-gray-700">
                        Foto de perfil
                        <span class="font-normal text-gray-400">(opcional)</span>
                    </label>

                    <input
                        id="profile_img"
                        type="file"
                        name="profile_img"
                        accept="image/png, image/jpeg, image/jpg, image/webp"
                        class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-violet-100 file:px-3 file:py-2 file:font-medium file:text-violet-700 hover:file:bg-violet-200"
                    >

                    <x-input-error :messages="$errors->get('profile_img')" class="mt-1" />
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button
                        type="button"
                        @click="showLoginModal = false"
                        class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-100"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="rounded-lg bg-[#02000f] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700"
                    >
                        Cadastrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rodapé -->
    <footer class="mt-16 border-t border-white/10 bg-[#02000f] px-6 py-4 text-white sm:px-10">
        <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-6 sm:grid-cols-3">

            <a
                href="/"
                aria-label="Ateliê Sonhador - Início"
                class="flex items-center justify-center gap-2 sm:justify-self-start"
            >
                <span class="text-3xl text-violet-400 transition-transform duration-300 hover:rotate-12">⚛</span>

                <span class="text-sm font-semibold tracking-wide">
                    Ateliê <span class="text-violet-400">Sonhador</span>
                </span>
            </a>

            <nav aria-label="Redes sociais" class="flex items-center justify-center gap-3">
                <a href="#" aria-label="TikTok" class="group flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 transition-all duration-300 hover:-translate-y-1 hover:border-violet-400/50 hover:bg-violet-500/10">
                    <img src="/icons/tiktok.svg" alt="" class="h-5 w-5 invert transition-opacity group-hover:opacity-70">
                </a>

                <a href="#" aria-label="YouTube" class="group flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 transition-all duration-300 hover:-translate-y-1 hover:border-violet-400/50 hover:bg-violet-500/10">
                    <img src="/icons/youtube.svg" alt="" class="h-5 w-5 invert transition-opacity group-hover:opacity-70">
                </a>

                <a href="#" aria-label="Instagram" class="group flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 transition-all duration-300 hover:-translate-y-1 hover:border-violet-400/50 hover:bg-violet-500/10">
                    <img src="/icons/instagram.svg" alt="" class="h-5 w-5 invert transition-opacity group-hover:opacity-70">
                </a>

                <a href="#" aria-label="LinkedIn" class="group flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 transition-all duration-300 hover:-translate-y-1 hover:border-violet-400/50 hover:bg-violet-500/10">
                    <img src="/icons/linkedIn.svg" alt="" class="h-5 w-5 invert transition-opacity group-hover:opacity-70">
                </a>
            </nav>

            <div class="text-center text-xs leading-relaxed text-white/50 sm:justify-self-end sm:text-right">
                <p>&copy; 2026 <span class="text-white/80">Ateliê Sonhador</span></p>
            </div>
        </div>
    </footer>

    <!-- Alpine.js -->
    <script>
        function galleryApp() {
            return {
                photos: [],
                searchQuery: '',
                searchError: '',
                loadingPhotos: false,

                user: @json(auth()->user()),

                showLoginModal: {{ $errors->any() ? 'true' : 'false' }},
                showUploadModal: false,
                authMode: @json(old('name') || old('username') ? 'register' : 'login'),

                openProfileModal: false,

                stats: {
                    likes: 0,
                    publications: 0
                },
                statsLoading: false,
                statsError: '',

                uploading: false,
                uploadError: '',
                likingIds: [],

                photoForm: {
                    title: '',
                    location: '',
                    image: null
                },

                async readResponse(response) {
                    const text = await response.text();

                    if (!text) {
                        return {};
                    }

                    try {
                        return JSON.parse(text);
                    } catch {
                        return { message: text };
                    }
                },

                async uploadPhoto() {
                    if (!this.user || this.user.role !== 'fotografo') {
                        this.uploadError = 'Apenas fotógrafos podem publicar fotos.';
                        return;
                    }

                    if (!this.photoForm.image) {
                        this.uploadError = 'Selecione uma imagem.';
                        return;
                    }

                    const formData = new FormData();
                    formData.append('title', this.photoForm.title);
                    formData.append('location', this.photoForm.location);
                    formData.append('image', this.photoForm.image);

                    this.uploading = true;
                    this.uploadError = '';

                    try {
                        const response = await fetch('/photos', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await this.readResponse(response);

                        if (!response.ok) {
                            let message = data.message || 'Não foi possível publicar a foto.';

                            if (data.errors) {
                                message = Object.values(data.errors).flat().join('\n');
                            }

                            throw new Error(message);
                        }

                        this.photoForm = {
                            title: '',
                            location: '',
                            image: null
                        };

                        const fileInput = document.getElementById('photo_image');
                        if (fileInput) {
                            fileInput.value = '';
                        }

                        this.showUploadModal = false;

                        await this.fetchPhotos();

                        if (this.openProfileModal) {
                            await this.fetchStats();
                        }
                    } catch (error) {
                        console.error('Erro ao publicar foto:', error);
                        this.uploadError = error.message || 'Erro ao publicar foto.';
                    } finally {
                        this.uploading = false;
                    }
                },

                closeUploadModal() {
                    this.showUploadModal = false;
                    this.uploadError = '';
                },

                async fetchPhotos() {
                    this.loadingPhotos = true;
                    this.searchError = '';

                    try {
                        const response = await fetch(
                            '/photos?search=' + encodeURIComponent(this.searchQuery),
                            {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            }
                        );

                        if (!response.ok) {
                            throw new Error('Erro HTTP ' + response.status);
                        }

                        const data = await this.readResponse(response);

                        this.photos = Array.isArray(data)
                            ? data
                            : (Array.isArray(data.photos)
                                ? data.photos
                                : (Array.isArray(data.data) ? data.data : []));

                        if (this.photos.length === 0 && this.searchQuery.trim() !== '') {
                            this.searchError = 'Nenhum fotógrafo ou publicação encontrado.';
                        }
                    } catch (error) {
                        console.error('Erro ao carregar fotos:', error);
                        this.photos = [];
                        this.searchError = 'Não foi possível carregar as fotos.';
                    } finally {
                        this.loadingPhotos = false;
                    }
                },

                async toggleLike(photo) {
                    if (!this.user) {
                        this.showLoginModal = true;
                        this.authMode = 'login';
                        return;
                    }

                    if (this.likingIds.includes(photo.id)) {
                        return;
                    }

                    this.likingIds.push(photo.id);

                    try {
                        const response = await fetch(
                            `/publications/${photo.id}/like`,
                            {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            }
                        );

                        const data = await this.readResponse(response);

                        if (!response.ok) {
                            throw new Error(data.message || 'Erro HTTP ' + response.status);
                        }

                        photo.is_liked = data.is_liked;
                        photo.likes_count = data.likes_count;

                        if (this.openProfileModal && this.user.role === 'fotografo') {
                            await this.fetchStats();
                        }
                    } catch (error) {
                        console.error('Erro ao curtir publicação:', error);
                        alert(error.message || 'Não foi possível registrar a curtida.');
                    } finally {
                        this.likingIds = this.likingIds.filter(id => id !== photo.id);
                    }
                },

                async fetchStats() {
                    if (!this.user || this.user.role !== 'fotografo') {
                        return;
                    }

                    this.statsLoading = true;
                    this.statsError = '';

                    try {
                        /*
                         * As estatísticas são calculadas a partir da galeria carregada.
                         * Para números totais, inclusive com a busca ativa,
                         * o ideal é disponibilizar esses valores pelo backend.
                         */
                        const ownPhotos = this.photos.filter(photo =>
                            String(photo.photographer_id ?? photo.user_id) === String(this.user.id)
                        );

                        this.stats.publications = ownPhotos.length;
                        this.stats.likes = ownPhotos.reduce(
                            (total, photo) => total + Number(photo.likes_count || 0),
                            0
                        );
                    } catch (error) {
                        console.error('Erro ao calcular estatísticas:', error);
                        this.statsError = 'Não foi possível calcular as estatísticas.';
                    } finally {
                        this.statsLoading = false;
                    }
                }
            };
        }
    </script>
</body>
</html>
