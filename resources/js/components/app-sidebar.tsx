import { Link } from "@inertiajs/react"
import { Network } from "lucide-react"
import AppLogo from "@/components/app-logo"
import { NavFooter } from "@/components/nav-footer"
import { NavMain } from "@/components/nav-main"
import { NavUser } from "@/components/nav-user"
import {
	Sidebar,
	SidebarContent,
	SidebarFooter,
	SidebarHeader,
	SidebarMenu,
	SidebarMenuButton,
	SidebarMenuItem,
} from "@/components/ui/sidebar"
import { dashboard } from "@/routes"
import type { NavItem } from "@/types"

export const mainNavItems: NavItem[] = [
	{
		title: "Dashboard",
		href: "/dashboard",
		icon: Network,
	},
]

const footerNavItems: NavItem[] = []

export function AppSidebar() {
	return (
		<Sidebar
			side="right"
			collapsible="icon"
			variant="inset">
			<SidebarHeader>
				<div className="flex items-center">
					<SidebarMenu className="min-w-0 flex-1">
						<SidebarMenuItem>
							<SidebarMenuButton
								size="lg"
								asChild>
								<Link
									href={dashboard()}
									prefetch>
									<AppLogo />
								</Link>
							</SidebarMenuButton>
						</SidebarMenuItem>
					</SidebarMenu>
				</div>
			</SidebarHeader>

			<SidebarContent>
				<NavMain items={mainNavItems} />
			</SidebarContent>

			<SidebarFooter>
				<NavFooter
					items={footerNavItems}
					className="mt-auto"
				/>
				<NavUser />
			</SidebarFooter>
		</Sidebar>
	)
}
