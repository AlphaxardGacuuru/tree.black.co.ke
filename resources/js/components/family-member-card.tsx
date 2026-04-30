import { useApp } from "@/contexts/AppContext"
import { X } from "lucide-react"

type FamilyMemberCardProps = {
	name: string
	avatar?: string | null
	onDelete?: () => void
}

export default function FamilyMemberCard({
	name,
	avatar,
	onDelete,
}: FamilyMemberCardProps) {
	const { memberInitials } = useApp()

	return (
		<div className="group flex flex-col items-center gap-2 text-center">
			<div className="relative h-36 w-36">
				{/* Glass circle */}
				<div className="relative flex h-full w-full cursor-pointer flex-col items-center justify-center overflow-hidden rounded-full border border-gray-200 shadow-md backdrop-blur-[1px] dark:border-white/10 dark:border-b-gray-300/70">
					{/* Avatar or initials */}
					{avatar ? (
						<img
							src={avatar}
							className="h-full w-full object-cover"
							alt={name}
						/>
					) : (
						<span className="relative z-10 text-2xl font-semibold text-foreground drop-shadow">
							{memberInitials(name)}
						</span>
					)}
				</div>
				{onDelete && (
					<button
						type="button"
						onClick={onDelete}
						className="absolute -top-2 -right-2 z-10 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full bg-red-500 text-white opacity-100 shadow-lg transition-opacity hover:bg-red-600 md:opacity-0 md:group-hover:opacity-100"
						aria-label={`Delete ${name} node`}
						title="Delete node">
						<X className="h-4 w-4" />
					</button>
				)}
			</div>
			<p className="line-clamp-2 text-xs font-medium text-foreground">{name}</p>
		</div>
	)
}
