import { Head, usePage } from "@inertiajs/react"
import { useCallback, useEffect, useState } from "react"
import { toast } from "sonner"
import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogHeader,
	DialogTitle,
	DialogTrigger,
} from "@/components/ui/dialog"
import FamilyMemberCard from "@/components/family-member-card"
import { PlaceholderPattern } from "@/components/ui/placeholder-pattern"
import { Button } from "@/components/ui/button"
import { SelectContent, SelectField, SelectItem } from "@/components/ui/select"
import Axios from "@/lib/axios"
import {
	destroy as destroyRelationship,
	shareLink as shareRelationshipLink,
	store as storeRelationship,
} from "@/routes/family-relationships"
import { show as showFamilyTree } from "@/routes/family-trees"

// Page data types - Start
type FamilyMember = {
	id: string
	name: string
	email: string
	gender?: string | null
	avatar?: string | null
}

type FamilyRelationship = {
	id: string
	userId: string
	relatedUserId: string
	relationshipType: string
}

type FamilyTree = {
	id: string
	name: string
	createdBy: string
	creatorId: string
	creatorName: string
	creatorGender: string | null
	creatorAvatar: string | null
	members: FamilyMember[]
	relationships: FamilyRelationship[]
	father: FamilyTreeNode[]
	mother: FamilyTreeNode[]
	brothers: FamilyTreeNode[]
	sisters: FamilyTreeNode[]
	sons: FamilyTreeNode[]
	daughters: FamilyTreeNode[]
	paternalUncles: FamilyTreeNode[]
	paternalAunts: FamilyTreeNode[]
	maternalUncles: FamilyTreeNode[]
	maternalAunts: FamilyTreeNode[]
}

type FamilyTreeNode = {
	id: string
	userId: string
	relatedUserId: string
	relationshipType: string
	name: string
	avatar: string | null
}
// Page data types - End

const relationshipOptions = [
	"father",
	"mother",
	"brother",
	"sister",
	"son",
	"daughter",
	"wife",
	"husband",
]
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
// Utility helpers - End

export default function DashboardPage() {
	// Page identity and state - Start
	const { auth } = usePage().props as {
		auth?: {
			user?: {
				id?: string | number
				mainFamilyTreeId?: string | null
			} | null
		}
	}

	const currentUserId =
		auth?.user?.id !== undefined ? String(auth.user.id) : null
	const mainFamilyTreeId = auth?.user?.mainFamilyTreeId ?? null

	const [activeTree, setActiveTree] = useState<FamilyTree | null>(null)
	const [isInviteModalOpen, setIsInviteModalOpen] = useState(false)
	const [isRelationshipModalOpen, setIsRelationshipModalOpen] = useState(false)
	const [isDeleteConfirmOpen, setIsDeleteConfirmOpen] = useState(false)
	const [deleteRelationshipId, setDeleteRelationshipId] = useState<
		string | null
	>(null)
	const [inviteRelationshipType, setInviteRelationshipType] = useState("")
	const [relationshipType, setRelationshipType] = useState("")
	const [relationshipSourceUserId, setRelationshipSourceUserId] = useState(
		currentUserId ?? ""
	)
	const [relatedUserId, setRelatedUserId] = useState("")

	// Page identity and state - End

	// Network actions - Start
	// Tree loading - Start
	const loadTrees = useCallback(() => {
		if (!mainFamilyTreeId) {
			setActiveTree(null)

			return Promise.resolve()
		}

		return Axios.get<{ data: FamilyTree | null }>(
			showFamilyTree.url(mainFamilyTreeId)
		)
			.then((response) => {
				setActiveTree(response.data.data)
			})
			.catch((requestError) => {
				toast.error(normalizeErrorMessage(requestError))
			})
	}, [mainFamilyTreeId])
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
		}>(shareRelationshipLink.url(), {
			familyTreeId: activeTree.id,
			relationshipType: relationshipTypeToShare,
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

	// Relationship deletion - Start
	const openDeleteConfirm = (relationshipId: string) => {
		setDeleteRelationshipId(relationshipId)
		setIsDeleteConfirmOpen(true)
	}

	const handleDeleteRelationship = () => {
		if (!deleteRelationshipId) {
			return
		}

		setIsDeleteConfirmOpen(false)
		setDeleteRelationshipId(null)

		Axios.delete(destroyRelationship.url(deleteRelationshipId))
			.then((res) => {
				toast.success(res.data.message)
				loadTrees()
			})
			.catch((requestError) => {
				toast.error(normalizeErrorMessage(requestError))
			})
	}
	// Relationship deletion - End

	// Relationship form submission - Start
	const handleCreateRelationship = (
		event: React.FormEvent<HTMLFormElement>
	) => {
		event.preventDefault()

		if (!activeTree || !relationshipSourceUserId || !relatedUserId) {
			return
		}

		Axios.post(storeRelationship.url(), {
			familyTreeId: activeTree.id,
			userId: relationshipSourceUserId,
			relatedUserId: relatedUserId,
			relationshipType: relationshipType,
		})
			.then((res) => {
				toast.success(res.data.message)
				setIsRelationshipModalOpen(false)
				loadTrees()
			})
			.catch((requestError) => {
				toast.error(normalizeErrorMessage(requestError))
			})
	}

	const handleRelationshipSourceUserChange = (userId: string) => {
		setRelationshipSourceUserId(userId)

		if (relatedUserId === userId) {
			setRelatedUserId("")
		}
	}
	// Relationship form submission - End
	// Network actions - End

	// Derived data - Start
	const activeMembers = activeTree?.members ?? []

	useEffect(() => {
		void loadTrees()
	}, [loadTrees])

	useEffect(() => {
		if (currentUserId && !relationshipSourceUserId) {
			setRelationshipSourceUserId(currentUserId)
		}
	}, [currentUserId, relationshipSourceUserId])

	// Render output - Start
	return (
		<>
			{/* Page metadata - Start */}
			<Head title="Family Tree" />
			{/* Page metadata - End */}

			{/* Page shell - Start */}
			<div className="flex h-full flex-1 flex-col gap-4 overflow-x-hidden p-4">
				{/* Main family tree panel - Start */}
				<section className="relative rounded-xl border p-4">
					<PlaceholderPattern className="pointer-events-none absolute inset-0 size-full stroke-neutral-900/5 dark:stroke-neutral-100/5" />
					<PlaceholderPattern className="pointer-events-none absolute inset-0 size-full -scale-x-100 stroke-neutral-900/5 dark:stroke-neutral-100/5" />
					{/* Panel header - Start */}
					<div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
						{/* Panel title - Start */}
						<div>
							<h1 className="text-3xl font-light">Family Tree</h1>
						</div>
						{/* Panel title - End */}

						{/* Panel actions - Start */}
						<div className="flex flex-wrap items-center gap-2">
							{/* Invite member dialog - Start */}
							<Dialog
								open={isInviteModalOpen}
								onOpenChange={setIsInviteModalOpen}>
								{/* Trigger Invite Member Dialog - Start */}
								<DialogTrigger asChild>
									<Button
										className="cursor-pointer"
										disabled={!activeTree}>
										Invite Member
									</Button>
								</DialogTrigger>
								{/* Trigger Invite Member Dialog - End */}

								{/* Invite Member Dialog Content - Start */}
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
											placeholder="Select Relationship"
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
								{/* Invite Member Dialog Content - End */}
							</Dialog>
							{/* Invite member dialog - End */}

							{/* Manual relationship dialog - Start */}
							<Dialog
								open={isRelationshipModalOpen}
								onOpenChange={(open) => {
									setIsRelationshipModalOpen(open)

									if (open) {
										setRelationshipSourceUserId(currentUserId ?? "")
										setRelatedUserId("")
									}
								}}>
								{/* Manual relationship Trigger Start */}
								<DialogTrigger asChild>
									<Button
										variant="outline"
										className="cursor-pointer"
										disabled={!activeTree}>
										Add Relationship
									</Button>
								</DialogTrigger>
								{/* Manual relationship Trigger End */}

								{/* Manual relationship Content Start */}
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
											label="For family member"
											placeholder="Select member"
											value={relationshipSourceUserId}
											onValueChange={handleRelationshipSourceUserChange}>
											<SelectContent>
												{activeMembers.map((member) => (
													<SelectItem
														key={`source-${member.id}`}
														value={member.id}>
														{member.name}
													</SelectItem>
												))}
											</SelectContent>
										</SelectField>
										<SelectField
											label="Family member"
											placeholder="Select member"
											value={relatedUserId}
											onValueChange={setRelatedUserId}>
											<SelectContent>
												{activeMembers
													.filter(
														(member) => member.id !== relationshipSourceUserId
													)
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
														value={option}
														className="capitalize">
														{option}
													</SelectItem>
												))}
											</SelectContent>
										</SelectField>
										<Button
											type="submit"
											disabled={
												!activeTree ||
												!relationshipSourceUserId ||
												!relatedUserId
											}
											className="w-full cursor-pointer">
											Add relationship
										</Button>
									</form>
								</DialogContent>
								{/* Manual relationship Content End */}
							</Dialog>
							{/* Manual relationship dialog - End */}
						</div>
						{/* Panel actions - End */}
					</div>
					{/* Panel header - End */}

					{/* Tree Start */}
					{activeTree ? (
						<div className="my-6 flex flex-1 flex-col items-start gap-12">
							<div className="flex"></div>
							<div className="flex w-full flex-1 justify-center gap-12">
								<div className="flex flex-1 justify-end gap-12">
									{/* Paternal Uncles Start */}
									{activeTree.paternalUncles.map((uncle) => (
										<FamilyMemberCard
											key={uncle.id}
											name={uncle.name}
											avatar={uncle.avatar}
											onDelete={() => openDeleteConfirm(uncle.id)}
										/>
									))}
									{/* Paternal Uncles End */}
									{/* Paternal Aunts Start */}
									{activeTree.paternalAunts.map((aunt) => (
										<FamilyMemberCard
											key={aunt.id}
											name={aunt.name}
											avatar={aunt.avatar}
											onDelete={() => openDeleteConfirm(aunt.id)}
										/>
									))}
									{/* Paternal Aunts End */}
								</div>
								{/* Father Start */}
								<div className="flex justify-center gap-12">
									{activeTree.father.map((father) => (
										<FamilyMemberCard
											key={father.id}
											name={father.name}
											avatar={father.avatar}
											onDelete={() => openDeleteConfirm(father.id)}
										/>
									))}
									{/* Father End */}
									{/* Mother Start */}
									{activeTree.mother.map((mother) => (
										<FamilyMemberCard
											key={mother.id}
											name={mother.name}
											avatar={mother.avatar}
											onDelete={() => openDeleteConfirm(mother.id)}
										/>
									))}
									{/* Mother End */}
								</div>
								<div className="flex flex-1 justify-start gap-12">
									{/* Maternal Uncles Start */}
									{activeTree.maternalUncles.map((uncle) => (
										<FamilyMemberCard
											key={uncle.id}
											name={uncle.name}
											avatar={uncle.avatar}
											onDelete={() => openDeleteConfirm(uncle.id)}
										/>
									))}
									{/* Maternal Uncles End */}
									{/* Maternal Aunts Start */}
									{activeTree.maternalAunts.map((aunt) => (
										<FamilyMemberCard
											key={aunt.id}
											name={aunt.name}
											avatar={aunt.avatar}
											onDelete={() => openDeleteConfirm(aunt.id)}
										/>
									))}
									{/* Maternal Aunts End */}
								</div>
							</div>
							<div className="flex w-full flex-1 justify-center gap-12">
								{/* Brothers Start */}
								<div className="flex flex-1 justify-end gap-12">
									{activeTree.brothers.map((brother) => (
										<FamilyMemberCard
											key={brother.id}
											name={brother.name}
											avatar={brother.avatar}
											onDelete={() => openDeleteConfirm(brother.id)}
										/>
									))}
								</div>
								{/* Brothers End */}
								{/* Current User Start */}
								<FamilyMemberCard
									key={activeTree.creatorId}
									name={activeTree.creatorName}
									avatar={activeTree.creatorAvatar}
									onDelete={() => openDeleteConfirm(activeTree.creatorId)}
								/>
								{/* Current User End */}
								{/* Sisters Start */}
								<div className="flex flex-1 justify-start gap-12">
									{activeTree.sisters.map((sister) => (
										<FamilyMemberCard
											key={sister.id}
											name={sister.name}
											avatar={sister.avatar}
											onDelete={() => openDeleteConfirm(sister.id)}
										/>
									))}
								</div>
								{/* Sisters End */}
							</div>
							{/* Children Start */}
							<div className="flex w-full flex-1 justify-center gap-12">
								{activeTree.sons.map((son) => (
									<FamilyMemberCard
										key={son.id}
										name={son.name}
										avatar={son.avatar}
										onDelete={() => openDeleteConfirm(son.id)}
									/>
								))}
								{activeTree.daughters.map((daughter) => (
									<FamilyMemberCard
										key={daughter.id}
										name={daughter.name}
										avatar={daughter.avatar}
										onDelete={() => openDeleteConfirm(daughter.id)}
									/>
								))}
							</div>
							{/* Children End */}
							<div className="flex"></div>
						</div>
					) : (
						<div className="mt-6 text-center text-sm text-muted-foreground">
							No family tree found. Create one to get started.
						</div>
					)}
					{/* Tree End */}
				</section>
				{/* Main family tree panel - End */}
			</div>
			{/* Page shell - End */}

			{/* Remove relationship confirmation - Start */}
			<Dialog
				open={isDeleteConfirmOpen}
				onOpenChange={setIsDeleteConfirmOpen}>
				<DialogContent className="sm:max-w-sm">
					<DialogHeader>
						<DialogTitle>Remove Relationship</DialogTitle>
						<DialogDescription>
							This will remove the relationship between you and this family
							member. This action cannot be undone.
						</DialogDescription>
					</DialogHeader>
					<div className="flex justify-end gap-2 pt-2">
						<Button
							variant="outline"
							className="cursor-pointer"
							onClick={() => setIsDeleteConfirmOpen(false)}>
							Cancel
						</Button>
						<Button
							variant="destructive"
							className="cursor-pointer"
							onClick={handleDeleteRelationship}>
							Remove
						</Button>
					</div>
				</DialogContent>
			</Dialog>
			{/* Remove relationship confirmation - End */}
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
