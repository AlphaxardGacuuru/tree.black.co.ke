import { createInertiaApp } from "@inertiajs/react"
import { Workbox } from "workbox-window"
import { Toaster } from "@/components/ui/sonner"
import { TooltipProvider } from "@/components/ui/tooltip"
import { initializeTheme } from "@/hooks/use-appearance"
import AppLayout from "@/layouts/app-layout"
import AuthLayout from "@/layouts/auth-layout"
import SettingsLayout from "@/layouts/settings/layout"
import { AppProvider } from "@/contexts/AppContext"
import InstallAppOnboardingModal from "@/components/install-app-onboarding-modal"
import toast from "@/lib/toast"

const appName = import.meta.env.VITE_APP_NAME || "Laravel"

createInertiaApp({
	title: (title) => (title ? `${title} - ${appName}` : appName),
	layout: (name) => {
		switch (true) {
			case name === "welcome":
				return null
			case name.startsWith("auth/"):
				return AuthLayout
			case name.startsWith("settings/"):
				return [AppLayout, SettingsLayout]
			default:
				return AppLayout
		}
	},
	strictMode: true,
	withApp(app) {
		return (
			<AppProvider>
				<TooltipProvider delayDuration={0}>
					{app}
					<Toaster />
					<InstallAppOnboardingModal />
				</TooltipProvider>
			</AppProvider>
		)
	},
	progress: {
		color: "#4B5563",
	},
})

if ("serviceWorker" in navigator) {
	const wb = new Workbox("/sw.js")

	// A new service worker is installed and waiting — don't force a reload
	// mid-session (that can drop an in-progress draft or scroll position).
	// Let the user pick the moment via the toast instead.
	wb.addEventListener("waiting", () => {
		toast("A new version is available", {
			duration: Infinity,
			action: {
				label: "Refresh",
				onClick: () => {
					wb.addEventListener("controlling", () => {
						window.location.reload()
					})
					wb.messageSkipWaiting()
				},
			},
		})
	})

	wb.register().catch(() => {
		// Ignore registration failures and keep the web app functional.
	})
}

// This will set light / dark mode on load...
initializeTheme()
