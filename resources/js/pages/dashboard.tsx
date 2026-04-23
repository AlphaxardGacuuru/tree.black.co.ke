import { Head, usePage } from "@inertiajs/react"
import { Plus } from "lucide-react"
import { useEffect, useMemo, useState } from "react"
import { toast } from "sonner"
import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogHeader,
	DialogTitle,
	DialogTrigger,
} from "@/components/ui/dialog"
import { Button } from "@/components/ui/button"
import { SelectContent, SelectField, SelectItem } from "@/components/ui/select"
import Axios from "@/lib/axios"
import { toUrl } from "@/lib/utils"
import {
	shareLink as shareRelationshipLink,
	store as storeRelationship,
} from "@/routes/family-relationships"
import { index as listFamilyTrees } from "@/routes/family-trees"

// Page data types - Start
type FamilyMember = {
	id: string
	name: string
	email: string
	gender?: string | null
	avatar_url?: string | null
}

type FamilyRelationship = {
	id: string
	user_id: string
	related_user_id: string
	relationship_type: string
}

type FamilyTree = {
	id: string
	name: string
	created_by: string
	members: FamilyMember[]
	relationships: FamilyRelationship[]
}

type TreeRowNode = {
	id: string
	member?: FamilyMember
	isCurrentUser?: boolean
	placeholderLabel?: string
	relationHint?: "grandparent" | "parent" | "aunt" | "sibling" | "child"
}

type TreeRow = {
	label: string
	nodes: TreeRowNode[]
}

type ArchitectureNode = {
	rowLabel: string
	node: TreeRowNode
	x: number
	y: number
}

type ArchitectureEdge = {
	id: string
	from: { x: number; y: number }
	to: { x: number; y: number }
	label: string
}
// Page data types - End

// Visualization constants - Start
const ARCHITECTURE_WIDTH = 1470
const ROW_Y_POSITIONS = [120, 320, 520, 720]
const AVATAR_SIZE = 128
const PLACEHOLDER_SIZE = 128

const relationshipOptions = [
	"father",
	"mother",
	"parent",
	"child",
	"sibling",
	"aunt",
	"uncle",
	"cousin",
]

const parentTypes = new Set(["parent", "mother", "father"])
const childTypes = new Set(["child", "son", "daughter"])
const siblingTypes = new Set(["sibling"])
const auntTypes = new Set(["aunt-uncle", "aunt", "uncle"])
const niblingTypes = new Set(["niece-nephew", "niece", "nephew"])
// Visualization constants - End

// Utility helpers - Start
function normalizeErrorMessage(error: unknown): string {
	const fallback = "Something went wrong. Please try again."

	if (typeof error !== "object" || error === null) {
		return fallback
	}

	const maybeResponse = error as {
		response?: {
			data?: {
				message?: string
			}
		}
	}

	return maybeResponse.response?.data?.message ?? fallback
}

function memberInitials(name: string): string {
	return name
		.split(" ")
		.map((part) => part[0])
		.slice(0, 2)
		.join("")
		.toUpperCase()
}
// Utility helpers - End

export default function DashboardPage() {
	// Page identity and state - Start
	const { auth } = usePage().props as {
		auth?: { user?: { id?: string | number } | null }
	}

	const currentUserId =
		auth?.user?.id !== undefined ? String(auth.user.id) : null

	const [activeTree, setActiveTree] = useState<FamilyTree | null>(null)
	const [isInviteModalOpen, setIsInviteModalOpen] = useState(false)
	const [isRelationshipModalOpen, setIsRelationshipModalOpen] = useState(false)
	const [inviteRelationshipType, setInviteRelationshipType] =
		useState("sibling")
	const [relationshipType, setRelationshipType] = useState("sibling")
	const [relatedUserId, setRelatedUserId] = useState("")

	const [isLoading, setIsLoading] = useState(false)
	// Page identity and state - End

	// Network actions - Start
	// Tree loading - Start
	const loadTrees = () => {
		setIsLoading(true)

		Axios.get<{ data: FamilyTree | null }>(toUrl(listFamilyTrees()))
			.then((response) => setActiveTree(response.data.data))
			.catch((requestError) => {
				toast.error(normalizeErrorMessage(requestError))
			})
			.finally(() => setIsLoading(false))
	}
	// Tree loading - End

	// Invite sharing helpers - Start
	const shareInvitation = (payload: {
		share_url: string
		share_title: string
		share_text: string
	}) => {
		if (
			typeof navigator !== "undefined" &&
			typeof navigator.share === "function"
		) {
			return navigator.share({
				title: payload.share_title,
				text: payload.share_text,
				url: payload.share_url,
			})
		}

		if (typeof navigator !== "undefined" && navigator.clipboard) {
			return navigator.clipboard.writeText(payload.share_url)
		}

		return Promise.reject(new Error("Sharing is not available on this device."))
	}
	// Invite sharing helpers - End

	// Invite link creation - Start
	const triggerWebShare = (relationshipTypeToShare: string) => {
		if (!activeTree) {
			return
		}

		Axios.post<{
			data: { share_url: string; share_title: string; share_text: string }
		}>(toUrl(shareRelationshipLink()), {
			family_tree_id: activeTree.id,
			relationship_type: relationshipTypeToShare,
		})
			.then((response) => {
				return shareInvitation({
					share_url: response.data.data.share_url,
					share_title: response.data.data.share_title,
					share_text: response.data.data.share_text,
				})
			})
			.then(() => {
				toast.success("Invite link ready to share.")
				setIsInviteModalOpen(false)
			})
			.catch((requestError) => {
				toast.error(normalizeErrorMessage(requestError))
			})
	}
	// Invite link creation - End

	// Invitation form submission - Start
	const handleCreateInvitation = (event: React.FormEvent<HTMLFormElement>) => {
		event.preventDefault()

		triggerWebShare(inviteRelationshipType)
	}
	// Invitation form submission - End

	// Relationship form submission - Start
	const handleCreateRelationship = (
		event: React.FormEvent<HTMLFormElement>
	) => {
		event.preventDefault()

		if (!activeTree || !relatedUserId) {
			return
		}

		Axios.post(toUrl(storeRelationship()), {
			family_tree_id: activeTree.id,
			related_user_id: relatedUserId,
			relationship_type: relationshipType,
		})
			.then(() => {
				toast.success("Relationship created.")
				setIsRelationshipModalOpen(false)
				loadTrees()
			})
			.catch((requestError) => {
				toast.error(normalizeErrorMessage(requestError))
			})
	}
	// Relationship form submission - End
	// Network actions - End

	// Derived data - Start
	const activeMembers = activeTree?.members ?? []

	// Family hierarchy rows - Start
	const hierarchyRows = useMemo<TreeRow[]>(() => {
		if (!activeTree || !currentUserId) {
			return []
		}

		const memberMap = new Map(
			activeTree.members.map((member) => [member.id, member])
		)
		const fallbackSelf = activeTree.members[0]
		const selfMember = memberMap.get(currentUserId) ?? fallbackSelf

		if (!selfMember) {
			return []
		}

		const selfId = selfMember.id

		const parents = new Set<string>()
		const siblings = new Set<string>()
		const children = new Set<string>()
		const auntsUncles = new Set<string>()

		activeTree.relationships.forEach((edge) => {
			const type = edge.relationship_type.toLowerCase()

			if (edge.user_id === selfId) {
				if (childTypes.has(type)) {
					parents.add(edge.related_user_id)
				}

				if (parentTypes.has(type)) {
					children.add(edge.related_user_id)
				}

				if (siblingTypes.has(type)) {
					siblings.add(edge.related_user_id)
				}

				if (niblingTypes.has(type)) {
					auntsUncles.add(edge.related_user_id)
				}
			}

			if (edge.related_user_id === selfId) {
				if (parentTypes.has(type)) {
					parents.add(edge.user_id)
				}

				if (childTypes.has(type)) {
					children.add(edge.user_id)
				}

				if (siblingTypes.has(type)) {
					siblings.add(edge.user_id)
				}

				if (auntTypes.has(type)) {
					auntsUncles.add(edge.user_id)
				}
			}
		})

		const grandParents = new Set<string>()

		parents.forEach((parentId) => {
			activeTree.relationships.forEach((edge) => {
				const type = edge.relationship_type.toLowerCase()

				if (edge.user_id === parentId && childTypes.has(type)) {
					grandParents.add(edge.related_user_id)
				}

				if (edge.related_user_id === parentId && parentTypes.has(type)) {
					grandParents.add(edge.user_id)
				}
			})
		})

		const toNodes = (ids: Set<string>): TreeRowNode[] => {
			return Array.from(ids)
				.filter((id) => id !== selfId)
				.map((id) => memberMap.get(id))
				.filter((member): member is FamilyMember => Boolean(member))
				.map((member) => ({
					id: member.id,
					member,
				}))
		}

		const parentNodes = toNodes(parents)

		while (parentNodes.length < 2) {
			parentNodes.push({
				id: `placeholder-parent-${parentNodes.length}`,
				placeholderLabel: "Add Parent",
				relationHint: "parent",
			})
		}

		const grandParentNodes = toNodes(grandParents)

		while (grandParentNodes.length < 2) {
			grandParentNodes.push({
				id: `placeholder-grandparent-${grandParentNodes.length}`,
				placeholderLabel: "Add Grandparent",
				relationHint: "grandparent",
			})
		}

		const parentLevelNodes = [
			...parentNodes,
			...toNodes(auntsUncles),
			{
				id: "placeholder-aunt-uncle",
				placeholderLabel: "Add Aunt or Uncle",
				relationHint: "aunt" as const,
			},
		]

		const siblingsNodes = [
			{
				id: selfMember.id,
				member: selfMember,
				isCurrentUser: true,
			},
			...toNodes(siblings),
			{
				id: "placeholder-sibling",
				placeholderLabel: "Add Sibling",
				relationHint: "sibling" as const,
			},
		]

		const childrenNodes = [
			...toNodes(children),
			{
				id: "placeholder-child",
				placeholderLabel: "Add Child",
				relationHint: "child" as const,
			},
		]

		return [
			{ label: "Grandparents", nodes: grandParentNodes },
			{ label: "Parents, Aunts and Uncles", nodes: parentLevelNodes },
			{ label: "You and Siblings", nodes: siblingsNodes },
			{ label: "Children", nodes: childrenNodes },
		]
	}, [activeTree, currentUserId])
	// Family hierarchy rows - End

	// Placeholder node actions - Start
	const handleAddNodeHint = (relationHint: TreeRowNode["relationHint"]) => {
		if (!relationHint) {
			return
		}

		const inviteDefault =
			relationHint === "grandparent" ? "parent" : relationHint

		setInviteRelationshipType(inviteDefault)
		setRelationshipType(inviteDefault)
		void triggerWebShare(inviteDefault)
	}
	// Placeholder node actions - End

	// Node layout model - Start
	const architectureNodes = useMemo<ArchitectureNode[]>(() => {
		return hierarchyRows.flatMap((row, rowIndex) => {
			const rowY =
				ROW_Y_POSITIONS[rowIndex] ?? ROW_Y_POSITIONS[ROW_Y_POSITIONS.length - 1]
			const isSiblingsRow = row.label === "You and Siblings"

			if (!isSiblingsRow) {
				const totalNodes = row.nodes.length

				return row.nodes.map((node, nodeIndex) => {
					const x = ((nodeIndex + 1) * ARCHITECTURE_WIDTH) / (totalNodes + 1)

					return {
						rowLabel: row.label,
						node,
						x,
						y: rowY,
					}
				})
			}

			const currentUserNode = row.nodes.find((node) => node.isCurrentUser)

			if (!currentUserNode) {
				const totalNodes = row.nodes.length

				return row.nodes.map((node, nodeIndex) => ({
					rowLabel: row.label,
					node,
					x: ((nodeIndex + 1) * ARCHITECTURE_WIDTH) / (totalNodes + 1),
					y: rowY,
				}))
			}

			const otherNodes = row.nodes.filter((node) => !node.isCurrentUser)
			const leftCount = Math.floor(otherNodes.length / 2)
			const leftNodes = otherNodes.slice(0, leftCount)
			const rightNodes = otherNodes.slice(leftCount)
			const centerX = ARCHITECTURE_WIDTH / 2
			const leftBoundary = 210
			const rightBoundary = ARCHITECTURE_WIDTH - 210

			const placeNodes = (
				nodes: TreeRowNode[],
				startX: number,
				endX: number
			): ArchitectureNode[] => {
				if (nodes.length === 0) {
					return []
				}

				return nodes.map((node, nodeIndex) => ({
					rowLabel: row.label,
					node,
					x: startX + ((nodeIndex + 1) * (endX - startX)) / (nodes.length + 1),
					y: rowY,
				}))
			}

			return [
				...placeNodes(leftNodes, leftBoundary, centerX - 120),
				{
					rowLabel: row.label,
					node: currentUserNode,
					x: centerX,
					y: rowY,
				},
				...placeNodes(rightNodes, centerX + 120, rightBoundary),
			]
		})
	}, [hierarchyRows])
	// Node layout model - End

	// Edge layout model - Start
	const architectureEdges = useMemo<ArchitectureEdge[]>(() => {
		if (!activeTree) {
			return []
		}

		const nodeByMemberId = new Map<string, { x: number; y: number }>()

		architectureNodes.forEach((architectureNode) => {
			if (architectureNode.node.member) {
				nodeByMemberId.set(architectureNode.node.member.id, {
					x: architectureNode.x,
					y: architectureNode.y,
				})
			}
		})

		const seen = new Set<string>()
		const edges: ArchitectureEdge[] = []

		activeTree.relationships.forEach((relationship) => {
			const source = nodeByMemberId.get(relationship.user_id)
			const target = nodeByMemberId.get(relationship.related_user_id)

			if (!source || !target) {
				return
			}

			const a =
				relationship.user_id < relationship.related_user_id
					? relationship.user_id
					: relationship.related_user_id
			const b =
				relationship.user_id < relationship.related_user_id
					? relationship.related_user_id
					: relationship.user_id
			const pairKey = `${a}:${b}`

			if (seen.has(pairKey)) {
				return
			}

			seen.add(pairKey)

			edges.push({
				id: `${pairKey}:${relationship.relationship_type}`,
				from: source,
				to: target,
				label: relationship.relationship_type,
			})
		})

		const selfNode = architectureNodes.find(
			(architectureNode) => architectureNode.node.isCurrentUser
		)
		const firstParentLevelNode = architectureNodes.find(
			(architectureNode) =>
				architectureNode.rowLabel === "Parents, Aunts and Uncles"
		)

		architectureNodes.forEach((architectureNode) => {
			if (architectureNode.node.member || !architectureNode.node.relationHint) {
				return
			}

			const anchor = (() => {
				if (!selfNode) {
					return firstParentLevelNode
				}

				switch (architectureNode.node.relationHint) {
					case "grandparent":
						return firstParentLevelNode ?? selfNode
					case "parent":
					case "aunt":
						return selfNode
					case "sibling":
					case "child":
						return selfNode
					default:
						return selfNode
				}
			})()

			if (!anchor) {
				return
			}

			edges.push({
				id: `placeholder:${architectureNode.node.id}`,
				from: { x: anchor.x, y: anchor.y },
				to: { x: architectureNode.x, y: architectureNode.y },
				label: "add",
			})
		})

		return edges
	}, [activeTree, architectureNodes])
	// Edge layout model - End
	// Derived data - End

	useEffect(() => {
		void loadTrees()
	}, [])

	// Render output - Start
	return (
		<>
			{/* Page metadata - Start */}
			<Head title="Family Tree" />
			{/* Page metadata - End */}

			{/* Page shell - Start */}
			<div className="flex h-full flex-1 flex-col gap-4 overflow-x-hidden p-4">
				{/* Main family tree panel - Start */}
				<section className="rounded-xl bg-linear-to-br from-[#2F4A1C]/10 via-background to-[#2F4A1C]/5 p-4 backdrop-blur-sm dark:from-[#2F4A1C]/30 dark:via-background dark:to-[#2F4A1C]/15">
					{/* Panel header - Start */}
					<div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
						{/* Panel title - Start */}
						<div>
							<h2 className="text-base font-semibold">Family Tree</h2>
						</div>
						{/* Panel title - End */}

						{/* Panel actions - Start */}
						<div className="flex flex-wrap items-center gap-2">
							{/* Invite member dialog - Start */}
							<Dialog
								open={isInviteModalOpen}
								onOpenChange={setIsInviteModalOpen}>
								<DialogTrigger asChild>
									<Button
										className="cursor-pointer"
										disabled={!activeTree}>
										Invite Member
									</Button>
								</DialogTrigger>
								<DialogContent className="sm:max-w-md">
									<DialogHeader>
										<DialogTitle>Invite Member</DialogTitle>
										<DialogDescription>
											Create a relationship-specific share link and send it with
											your device share sheet.
										</DialogDescription>
									</DialogHeader>
									<form
										onSubmit={handleCreateInvitation}
										className="space-y-3">
										<SelectField
											label="Relationship"
											placeholder="Select relationship"
											value={inviteRelationshipType}
											onValueChange={setInviteRelationshipType}>
											<SelectContent>
												{relationshipOptions.map((option) => (
													<SelectItem
														key={option}
														value={option}
														className="capitalize">
														{option}
													</SelectItem>
												))}
											</SelectContent>
										</SelectField>
										<Button
											type="submit"
											disabled={!activeTree}
											className="w-full cursor-pointer">
											Share invite
										</Button>
									</form>
								</DialogContent>
							</Dialog>
							{/* Invite member dialog - End */}

							{/* Manual relationship dialog - Start */}
							<Dialog
								open={isRelationshipModalOpen}
								onOpenChange={setIsRelationshipModalOpen}>
								<DialogTrigger asChild>
									<Button
										variant="outline"
										className="cursor-pointer"
										disabled={!activeTree}>
										Add Relationship
									</Button>
								</DialogTrigger>
								<DialogContent className="sm:max-w-md">
									<DialogHeader>
										<DialogTitle>Manual Relationship</DialogTitle>
										<DialogDescription>
											Link two family members directly when the relationship
											already exists.
										</DialogDescription>
									</DialogHeader>
									<form
										onSubmit={handleCreateRelationship}
										className="space-y-3">
										<SelectField
											label="Family member"
											placeholder="Select member"
											value={relatedUserId}
											onValueChange={setRelatedUserId}>
											<SelectContent>
												{activeMembers
													.filter((member) => member.id !== currentUserId)
													.map((member) => (
														<SelectItem
															key={member.id}
															value={member.id}>
															{member.name}
														</SelectItem>
													))}
											</SelectContent>
										</SelectField>
										<SelectField
											label="Relationship"
											placeholder="Select relationship"
											value={relationshipType}
											onValueChange={setRelationshipType}>
											<SelectContent>
												{relationshipOptions.map((option) => (
													<SelectItem
														key={option}
														value={option}>
														{option}
													</SelectItem>
												))}
											</SelectContent>
										</SelectField>
										<Button
											type="submit"
											disabled={!activeTree || !relatedUserId}
											className="w-full cursor-pointer">
											Add relationship
										</Button>
									</form>
								</DialogContent>
							</Dialog>
							{/* Manual relationship dialog - End */}

							{/* Refresh action - Start */}
							<Button
								variant="outline"
								onClick={loadTrees}
								className="cursor-pointer"
								disabled={isLoading}>
								{isLoading ? "Refreshing..." : "Refresh"}
							</Button>
							{/* Refresh action - End */}
						</div>
						{/* Panel actions - End */}
					</div>
					{/* Panel header - End */}

					{/* Tree canvas wrapper - Start */}
					<div className="mt-4 overflow-x-auto">
						{/* Tree canvas - Start */}
						<div className="relative mx-auto h-210 w-367.5 rounded-xl bg-linear-to-b from-[#2F4A1C]/10 via-white/60 to-[#2F4A1C]/5 shadow-inner dark:from-[#2F4A1C]/25 dark:via-neutral-900/50 dark:to-[#2F4A1C]/15">
							{/* Relationship edges - Start */}
							<svg className="absolute inset-0 h-full w-full">
								{architectureEdges.map((edge) => {
									const midX = (edge.from.x + edge.to.x) / 2
									const midY = (edge.from.y + edge.to.y) / 2

									return (
										<g key={edge.id}>
											<line
												x1={edge.from.x}
												y1={edge.from.y}
												x2={edge.to.x}
												y2={edge.to.y}
												stroke="currentColor"
												strokeOpacity="0.4"
												strokeWidth="2"
												className="text-[#2F4A1C] dark:text-[#8FAE6D]"
											/>
											<text
												x={midX}
												y={midY - 6}
												textAnchor="middle"
												className="fill-[#2F4A1C] text-[10px] font-medium dark:fill-[#A3C07E]">
												{edge.label === "add" ? "+" : edge.label}
											</text>
										</g>
									)
								})}
							</svg>
							{/* Relationship edges - End */}

							{/* Row labels - Start */}
							{hierarchyRows.map((row, rowIndex) => (
								<div
									key={`label-${row.label}`}
									className="absolute left-4 text-[11px] font-semibold tracking-wide text-[#2F4A1C] uppercase dark:text-[#8FAE6D]"
									style={{ top: `${(ROW_Y_POSITIONS[rowIndex] ?? 0) - 34}px` }}>
									{row.label}
								</div>
							))}
							{/* Row labels - End */}

							{/* Member and placeholder nodes - Start */}
							{architectureNodes.map((architectureNode) => {
								const { node, x, y } = architectureNode

								if (node.member) {
									return (
										<div
											key={node.id}
											className="absolute flex w-36 -translate-x-1/2 flex-col items-center gap-3 text-center"
											style={{
												left: `${x}px`,
												top: `${y - AVATAR_SIZE / 2}px`,
											}}>
											<div
												className={`relative inline-flex h-32 w-32 items-center justify-center overflow-hidden rounded-full border text-2xl font-semibold shadow ${
													node.isCurrentUser
														? "border-[#2F4A1C]/70 bg-[#2F4A1C]/15 text-[#2F4A1C] dark:bg-[#2F4A1C]/40 dark:text-[#DCE9CC]"
														: "border-zinc-200 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
												}`}
												title={`${node.member.name} (${node.member.email})`}>
												{node.member.avatar_url ? (
													<img
														src={node.member.avatar_url}
														alt={node.member.name}
														className="h-full w-full object-cover"
													/>
												) : (
													memberInitials(node.member.name)
												)}
											</div>
											<p className="line-clamp-2 text-xs font-medium text-foreground">
												{node.member.name}
											</p>
										</div>
									)
								}

								return (
									<button
										key={node.id}
										type="button"
										onClick={() => handleAddNodeHint(node.relationHint)}
										className="absolute flex w-36 -translate-x-1/2 cursor-pointer flex-col items-center gap-3 text-center"
										style={{
											left: `${x}px`,
											top: `${y - PLACEHOLDER_SIZE / 2}px`,
										}}>
										<span className="inline-flex h-32 w-32 items-center justify-center rounded-full border border-dashed border-[#2F4A1C]/60 bg-[#2F4A1C]/10 text-[#2F4A1C] dark:border-[#8FAE6D]/70 dark:bg-[#2F4A1C]/20 dark:text-[#A3C07E]">
											<Plus className="h-8 w-8" />
										</span>
										<span className="text-xs font-medium text-[#2F4A1C] dark:text-[#8FAE6D]">
											{node.placeholderLabel}
										</span>
									</button>
								)
							})}
							{/* Member and placeholder nodes - End */}
						</div>
						{/* Tree canvas - End */}
					</div>
					{/* Tree canvas wrapper - End */}
				</section>
				{/* Main family tree panel - End */}
			</div>
			{/* Page shell - End */}
		</>
	)
	// Render output - End
}

// Layout metadata - Start
DashboardPage.layout = {
	breadcrumbs: [
		{
			title: "Dashboard",
			href: "/dashboard",
		},
	],
}
// Layout metadata - End
