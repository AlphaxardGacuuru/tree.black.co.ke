import { Head, Link, usePage } from "@inertiajs/react"
import type { LucideIcon } from "lucide-react"
import {
	ArrowRight,
	CheckCircle2,
	Link2,
	Network,
	ShieldCheck,
	Sparkles,
} from "lucide-react"
import AppLogo from "@/components/app-logo"
import { dashboard, login, register } from "@/routes"

type WelcomeProps = {
	canRegister?: boolean
}

type FeatureItem = {
	title: string
	description: string
	icon: LucideIcon
}

const features: FeatureItem[] = [
	{
		title: "Visual Family Mapping",
		description:
			"See relatives by generation and branch with a clear, interactive family structure.",
		icon: Network,
	},
	{
		title: "Relationship Invitations",
		description:
			"Invite parents, siblings, cousins, and extended family with shareable relationship links.",
		icon: Link2,
	},
	{
		title: "Private by Design",
		description:
			"Secure authentication and controlled access keep your family graph protected.",
		icon: ShieldCheck,
	},
]

export default function Welcome({ canRegister = true }: WelcomeProps) {
	const { auth } = usePage().props as { auth?: { user?: unknown } }

	return (
		<>
			<Head title="Black Family Tree" />

			<div className="relative min-h-screen overflow-hidden bg-background text-foreground">
				<div className="pointer-events-none absolute inset-0">
					<div className="absolute -top-28 left-[12%] size-72 rounded-full bg-primary/10 blur-3xl motion-safe:animate-pulse" />
					<div className="absolute top-[24%] -right-32 size-80 rounded-full bg-chart-2/20 blur-3xl motion-safe:animate-pulse" />
					<div className="absolute -bottom-32 left-[38%] size-80 rounded-full bg-chart-4/15 blur-3xl motion-safe:animate-pulse" />
				</div>

				<header className="relative mx-auto flex w-full max-w-7xl items-center justify-between px-6 pt-6 lg:px-10">
					<AppLogo
						variant="full"
						className="h-16 w-auto dark:invert"
					/>

					<nav className="flex items-center gap-2 sm:gap-3">
						{auth?.user ? (
							<Link
								href={dashboard()}
								className="inline-flex items-center gap-2 rounded-md border border-border/80 bg-card/70 px-4 py-2 text-sm font-medium shadow-xs backdrop-blur transition-colors hover:bg-accent">
								Open Dashboard
								<ArrowRight className="size-4" />
							</Link>
						) : (
							<>
								<Link
									href={login()}
									className="inline-flex items-center rounded-md border border-transparent px-4 py-2 text-sm font-medium transition-colors hover:border-border hover:bg-accent">
									Log in
								</Link>
								{canRegister && (
									<Link
										href={register()}
										className="inline-flex items-center rounded-md border border-border/80 bg-card/70 px-4 py-2 text-sm font-medium shadow-xs backdrop-blur transition-colors hover:bg-accent">
										Create account
									</Link>
								)}
							</>
						)}
					</nav>
				</header>

				<main className="relative mx-auto grid w-full max-w-7xl gap-10 px-6 pt-10 pb-14 lg:grid-cols-2 lg:items-center lg:gap-16 lg:px-10 lg:pt-16">
					<section className="space-y-6 duration-700 motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-6">
						<span className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.16em] text-primary uppercase">
							<Sparkles className="size-3.5" />
							Built For Family History
						</span>

						<h1 className="max-w-2xl text-4xl font-semibold tracking-tight text-balance sm:text-5xl lg:text-6xl">
							Build and preserve your Family Tree with confidence.
						</h1>

						<p className="max-w-xl text-base text-muted-foreground sm:text-lg">
							Map immediate and extended relatives, link real relationships, and
							keep your family story connected in one place.
						</p>

						<div className="flex flex-wrap items-center gap-3 pt-2">
							<Link
								href={auth?.user ? dashboard() : login()}
								className="inline-flex items-center gap-2 rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-transform hover:-translate-y-0.5 hover:bg-primary/90">
								{auth?.user
									? "Open family dashboard"
									: "Start your family tree"}
								<ArrowRight className="size-4" />
							</Link>
							{!auth?.user && canRegister ? (
								<Link
									href={register()}
									className="inline-flex items-center rounded-md border border-border/80 bg-card/80 px-5 py-2.5 text-sm font-semibold shadow-xs backdrop-blur transition-colors hover:bg-accent">
									Create family account
								</Link>
							) : null}
						</div>

						<div className="grid grid-cols-2 gap-3 sm:max-w-md">
							<div className="rounded-xl border border-border/70 bg-card/80 p-4 shadow-xs backdrop-blur delay-150 duration-700 motion-safe:animate-in motion-safe:fade-in">
								<p className="text-2xl font-semibold">All Branches</p>
								<p className="text-sm text-muted-foreground">
									organized by relationship group
								</p>
							</div>
							<div className="rounded-xl border border-border/70 bg-card/80 p-4 shadow-xs backdrop-blur delay-300 duration-700 motion-safe:animate-in motion-safe:fade-in">
								<p className="text-2xl font-semibold">Invite-Ready</p>
								<p className="text-sm text-muted-foreground">
									link and connect relatives quickly
								</p>
							</div>
						</div>
					</section>

					<section className="relative delay-200 duration-900 motion-safe:animate-in motion-safe:slide-in-from-bottom-10 motion-safe:fade-in">
						<div className="rounded-2xl border border-border/80 bg-card/85 p-5 shadow-xl backdrop-blur sm:p-6">
							<div className="mb-5 flex items-center justify-between">
								<h2 className="text-sm font-semibold tracking-[0.14em] text-muted-foreground uppercase">
									Tree Snapshot
								</h2>
								<span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-medium text-emerald-600 dark:text-emerald-300">
									<CheckCircle2 className="size-3.5" />
									Syncing family links
								</span>
							</div>

							<div className="space-y-4">
								<div className="rounded-xl border border-border/70 bg-background/90 p-4">
									<p className="text-xs tracking-[0.14em] text-muted-foreground uppercase">
										Family Members
									</p>
									<p className="mt-2 text-3xl font-semibold tracking-tight">
										24 Linked
									</p>
									<div className="mt-3 h-2 overflow-hidden rounded-full bg-muted">
										<div className="h-full w-4/5 rounded-full bg-chart-1 motion-safe:animate-pulse" />
									</div>
								</div>

								<div className="grid gap-3 sm:grid-cols-2">
									<div className="rounded-xl border border-border/70 bg-background/90 p-4">
										<p className="text-xs tracking-[0.14em] text-muted-foreground uppercase">
											Largest Branch
										</p>
										<p className="mt-2 text-lg font-semibold">Paternal Side</p>
										<p className="text-sm text-muted-foreground">
											8 relatives connected
										</p>
									</div>
									<div className="rounded-xl border border-border/70 bg-background/90 p-4">
										<p className="text-xs tracking-[0.14em] text-muted-foreground uppercase">
											Latest Invite
										</p>
										<p className="mt-2 text-lg font-semibold text-emerald-600 dark:text-emerald-300">
											Accepted
										</p>
										<p className="text-sm text-muted-foreground">
											1 minute ago
										</p>
									</div>
								</div>
							</div>
						</div>

						<div className="pointer-events-none absolute -right-4 -bottom-4 rounded-xl border border-border/60 bg-card/70 px-3 py-2 text-xs text-muted-foreground shadow-md backdrop-blur delay-700 duration-700 motion-safe:animate-in motion-safe:fade-in">
							Relationship graph is up to date
						</div>
					</section>
				</main>

				<section className="relative mx-auto w-full max-w-7xl px-6 pb-14 lg:px-10">
					<div className="grid gap-4 md:grid-cols-3">
						{features.map((feature, index) => {
							const Icon = feature.icon

							return (
								<article
									key={feature.title}
									className="rounded-2xl border border-border/70 bg-card/80 p-5 shadow-xs backdrop-blur duration-700 motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-4"
									style={{ animationDelay: `${index * 150}ms` }}>
									<div className="mb-3 inline-flex rounded-lg bg-primary/10 p-2.5 text-primary">
										<Icon className="size-5" />
									</div>
									<h3 className="text-lg font-semibold tracking-tight">
										{feature.title}
									</h3>
									<p className="mt-2 text-sm text-muted-foreground">
										{feature.description}
									</p>
								</article>
							)
						})}
					</div>
				</section>
			</div>
		</>
	)
}
