import { Head, Link, router, usePage } from '@inertiajs/react';
import { lazy, Suspense } from 'react';
import { ColumnEmblem } from '@/components/library/column-emblem';
import { capsButton } from '@/components/library/form-controls';
import { GreekKey } from '@/components/library/greek-key';
import InfiniteMenu from '@/components/reactbits/InfiniteMenu';
import ScrollExpand from '@/components/reactbits/ScrollExpand';
import { Button } from '@/components/ui/button';
import { LOAN_DAYS, MAX_ACTIVE_LOANS } from '@/lib/library';
import { dashboard, login } from '@/routes';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */
import { index as catalog } from '@/routes/catalog';
import type { Book } from '@/types';

// Three.js is large, so the 3D viewer downloads after the page has appeared
const ModelViewer = lazy(() => import('@/components/reactbits/ModelViewer'));

const STEPS = [
    {
        numeral: 'I',
        title: 'Seek',
        text: 'Search by title, author or ISBN, or wander a mythology.',
    },
    {
        numeral: 'II',
        title: 'Request',
        text: `Click Borrow and the librarian sees it at once. Up to ${MAX_ACTIVE_LOANS} books at a time.`,
    },
    {
        numeral: 'III',
        title: 'Collect',
        text: `Pick it up at the desk. It is yours for ${LOAN_DAYS} days.`,
    },
];

export default function Welcome({
    books,
    totals,
}: {
    books: Book[];
    totals: { books: number; categories: number };
}) {
    const { auth } = usePage().props;

    // Readers go to the catalog; everyone else is asked to log in first
    const openBook = (title?: string) =>
        router.visit(
            auth.user ? catalog({ query: { search: title } }) : login(),
        );

    const menuItems = books.map((book) => ({
        image: book.cover_url ?? '',
        title: book.title,
        description: `${book.author} · ${book.category?.name ?? ''}`,
    }));

    return (
        <>
            <Head title="Welcome" />
            <div className="min-h-screen bg-ink text-bone">
                <header className="border-b border-line">
                    <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-4">
                        <Link href="/" className="flex items-center gap-3">
                            <span className="flex size-10 items-center justify-center rounded-full border border-copper text-copper">
                                <ColumnEmblem className="size-5" />
                            </span>
                            <span className="font-display text-lg font-semibold tracking-[0.04em]">
                                Marguerite’s Athenaeum
                            </span>
                        </Link>
                        <nav
                            aria-label="Account"
                            className="flex items-center gap-2"
                        >
                            {auth.user ? (
                                <Button asChild className={capsButton}>
                                    <Link href={dashboard()}>Dashboard</Link>
                                </Button>
                            ) : (
                                <>
                                    <Button
                                        asChild
                                        variant="ghost"
                                        className={capsButton}
                                    >
                                        <Link href={login()}>Log in</Link>
                                    </Button>
                                    {/* @chisel-registration */}
                                    <Button asChild className={capsButton}>
                                        <Link href={register()}>
                                            Get a reader’s card
                                        </Link>
                                    </Button>
                                    {/* @end-chisel-registration */}
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                {/* Hero, framed like a book cover */}
                <section className="mx-auto max-w-6xl px-6 pt-14 pb-16">
                    <div className="border border-copper p-1.5">
                        <div className="flex flex-wrap items-center gap-12 border border-line-strong px-8 py-12 md:px-12 md:py-14">
                            <div className="flex min-w-0 flex-[1_1_420px] flex-col gap-5">
                                <span className="font-caps text-xs font-semibold tracking-[0.22em] text-copper uppercase">
                                    A lending library of myths
                                </span>
                                <h1 className="font-display text-5xl leading-[1.04] font-semibold tracking-[0.02em] text-balance md:text-6xl">
                                    Marguerite’s Athenaeum
                                </h1>
                                <p className="max-w-lg text-xl text-bone-soft italic md:text-2xl">
                                    Timeless tales of gods and heroes, from
                                    Olympus to the nine worlds. Request a book
                                    in one click and collect it at the desk.
                                </p>
                                <div className="mt-1 flex flex-wrap items-center gap-5">
                                    <Button
                                        asChild
                                        size="lg"
                                        className={`${capsButton} h-12 px-6`}
                                    >
                                        <Link
                                            href={
                                                auth.user
                                                    ? dashboard()
                                                    : register()
                                            }
                                        >
                                            {auth.user
                                                ? 'Go to your dashboard'
                                                : 'Get a reader’s card'}
                                        </Link>
                                    </Button>
                                    <a
                                        href="#shelf"
                                        className="text-lg text-copper-bright underline-offset-4 hover:underline"
                                    >
                                        Browse the shelves
                                    </a>
                                </div>
                            </div>

                            <div className="flex flex-[0_1_340px] flex-col items-center gap-2">
                                <Suspense
                                    fallback={
                                        <div className="flex h-[360px] w-full items-center justify-center">
                                            <ColumnEmblem className="size-16 animate-pulse text-copper" />
                                        </div>
                                    }
                                >
                                    <ModelViewer
                                        url="/models/athenaeum-book.gltf"
                                        width="100%"
                                        height={360}
                                        environment="sunset"
                                        environmentIntensity={1.4}
                                        autoRotateSpeed={0.35}
                                        startAngle={-28}
                                        tilt={12}
                                        zoom={false}
                                        shadowOpacity={0.6}
                                        fit={0.75}
                                    />
                                </Suspense>
                                <span className="font-caps text-[11px] font-semibold tracking-[0.16em] text-faded uppercase">
                                    Drag to turn the book
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <GreekKey />

                {/* The frame grows to fill the screen as the visitor scrolls */}
                <ScrollExpand
                    useWindowScroll
                    src={books[0]?.cover_url?.replace('-M.jpg', '-L.jpg') ?? ''}
                    alt={books[0] ? `Cover of ${books[0].title}` : ''}
                    title="Spin the Shelf"
                    scrollHint="Scroll to open the shelf"
                    startWidth={34}
                    startHeight={52}
                    startRadius={2}
                    overlayScrim={0.6}
                    className="[&_[class*='font-bold']]:font-display [&_[class*='font-bold']]:font-semibold [&_[class*='font-bold']]:tracking-[0.04em]"
                >
                    <p className="font-caps text-xs font-semibold tracking-[0.22em] text-copper-bright uppercase">
                        {totals.books} books · {totals.categories} mythologies
                    </p>
                    <p className="mt-4 max-w-xl font-display text-3xl leading-tight font-semibold text-bone md:text-5xl">
                        Every god, hero and monster, one shelf away
                    </p>
                </ScrollExpand>

                {/* ReactBits Infinite Menu: drag the sphere of covers */}
                <section
                    id="shelf"
                    aria-label="Browse the shelves"
                    className="border-y border-line bg-ink-deep"
                >
                    <div className="mx-auto flex max-w-6xl flex-wrap items-baseline justify-between gap-3 px-6 pt-14">
                        <h2 className="font-display text-3xl font-semibold">
                            On the shelf
                        </h2>
                        <span className="text-dust">
                            Drag to spin. Click the arrow to open a book.
                        </span>
                    </div>
                    <div className="relative h-[640px]">
                        <InfiniteMenu
                            items={menuItems}
                            theme="dark"
                            accentColor="#c8804f"
                            backgroundColor="#110e0b"
                            onItemClick={(item) => openBook(item.title)}
                            className="[&_h2]:font-display [&_h2]:font-semibold [&_h2]:tracking-[0.02em] [&_p]:font-sans [&_p]:text-bone-soft"
                        />
                    </div>
                </section>

                <section className="mx-auto flex max-w-6xl flex-col gap-9 px-6 py-20">
                    <h2 className="text-center font-display text-4xl font-semibold tracking-[0.02em]">
                        How borrowing works
                    </h2>
                    <ol className="flex flex-wrap border-y border-t-copper border-b-line">
                        {STEPS.map((step, index) => (
                            <li
                                key={step.title}
                                className={`flex flex-[1_1_260px] flex-col items-center gap-2.5 px-7 py-8 text-center ${index > 0 ? 'border-l border-line' : ''}`}
                            >
                                <span className="font-display text-3xl font-semibold text-copper">
                                    {step.numeral}
                                </span>
                                <span className="font-display text-xl font-semibold">
                                    {step.title}
                                </span>
                                <span className="text-lg text-bone-soft">
                                    {step.text}
                                </span>
                            </li>
                        ))}
                    </ol>
                </section>

                <footer className="border-t border-line">
                    <div className="mx-auto flex max-w-6xl flex-wrap justify-between gap-3 px-6 py-6 text-dust">
                        <span className="font-display tracking-[0.04em]">
                            Marguerite’s Athenaeum
                        </span>
                        <span>Open Monday to Saturday, 8 AM – 5 PM</span>
                    </div>
                </footer>
            </div>
        </>
    );
}
