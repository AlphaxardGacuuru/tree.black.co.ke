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
import { index as indexFamilyRelationships } from "@/routes/family-relationships"
import FamilyGroup from "@/components/family-group"

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

type FamilyTreeNode = {
	id: string
	userId: string
	relatedUserId: string
	relationshipType: string
	name: string
	avatar: string | null
}

type FamilyTree = {
	members: FamilyMember[]
	relationships: FamilyRelationship[]
	father: FamilyTreeNode[]
	mother: FamilyTreeNode[]
	brothers: FamilyTreeNode[]
	sisters: FamilyTreeNode[]
	spouse: FamilyTreeNode[]
	sons: FamilyTreeNode[]
	daughters: FamilyTreeNode[]
	paternalUncles: FamilyTreeNode[]
	paternalAunts: FamilyTreeNode[]
	maternalUncles: FamilyTreeNode[]
	maternalAunts: FamilyTreeNode[]
	paternalCousins: FamilyTreeNode[]
	maternalCousins: FamilyTreeNode[]
	paternalNephews: FamilyTreeNode[]
	paternalNieces: FamilyTreeNode[]
	maternalNephews: FamilyTreeNode[]
	maternalNieces: FamilyTreeNode[]
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
				id: string | number
				name: string | null
				avatar: string | null
			} | null
		}
	}

	const currentUserId =
		auth?.user?.id !== undefined ? String(auth.user.id) : null

	const [tree, setTree] = useState<FamilyTree | null>(null)
	const [isInviteModalOpen, setIsInviteModalOpen] = useState(false)
	const [isRelationshipModalOpen, setIsRelationshipModalOpen] = useState(false)
	const [isDeleteConfirmOpen, setIsDeleteConfirmOpen] = useState(false)
	const [deleteRelationshipName, setDeleteRelationshipName] = useState("")
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
		return Axios.get<{ data: FamilyTree | null }>(
			indexFamilyRelationships.url()
		)
			.then((response) => {
				setTree(response.data.data)
			})
			.catch((requestError) => {
				toast.error(normalizeErrorMessage(requestError))
			})
	}, [])
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
		if (!tree) {
			return
		}

		Axios.post<{
			data: { share_url: string; share_title: string; share_text: string }
		}>(shareRelationshipLink.url(), {
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
	const openDeleteConfirm = (
		relationshipId: string,
		relationshipName: string
	) => {
		setDeleteRelationshipId(relationshipId)
		setDeleteRelationshipName(relationshipName)
		setIsDeleteConfirmOpen(true)
	}

	const handleDeleteRelationship = () => {
		if (!deleteRelationshipId) {
			return
		}

		setIsDeleteConfirmOpen(false)
		setDeleteRelationshipId(null)
		setDeleteRelationshipName("")

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

		if (!tree || !relationshipSourceUserId || !relatedUserId) {
			return
		}

		Axios.post(storeRelationship.url(), {
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
	const activeMembers = tree?.members ?? []

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
			<div className="flex h-full flex-1 flex-col gap-4 overflow-hidden p-4">
				{/* Main family tree panel - Start */}
				<section className="relative flex min-h-0 flex-1 flex-col rounded-xl border p-4">
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
										disabled={!tree}>
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
											disabled={!tree}
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
										disabled={!tree}>
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
												!tree || !relationshipSourceUserId || !relatedUserId
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
					{tree ? (
						<div className="my-6 flex min-h-0 flex-1 flex-col items-start gap-8 overflow-auto">
							<div className="flex"></div>
							<div className="grid w-full grid-cols-[1fr_auto_1fr] items-center gap-8">
								<div className="flex items-center justify-end gap-8">
									<FamilyGroup title="Paternal Uncles">
									{/* Paternal Uncles Start */}
									{tree.paternalUncles.map((uncle) => (
										<FamilyMemberCard
											key={uncle.id}
											relationship="Uncle"
											name={uncle.name}
											avatar={uncle.avatar}
											onDelete={() => openDeleteConfirm(uncle.id, uncle.name)}
										/>
									))}
									{/* Paternal Uncles End */}
									{/* Paternal Aunts Start */}
									{tree.paternalAunts.map((aunt) => (
										<FamilyMemberCard
											key={aunt.id}
											relationship="Aunt"
											name={aunt.name}
											avatar={aunt.avatar}
											onDelete={() => openDeleteConfirm(aunt.id, aunt.name)}
										/>
									))}
									{/* Paternal Aunts End */}
									</FamilyGroup>
								</div>
								{/* Father Start */}
								<FamilyGroup title="Parents">
									{tree.father.map((father) => (
										<FamilyMemberCard
											key={father.id}
											relationship="Father"
											name={father.name}
											avatar={father.avatar}
											onDelete={() => openDeleteConfirm(father.id, father.name)}
										/>
									))}
									{/* Father End */}
									{/* Mother Start */}
									{tree.mother.map((mother) => (
										<FamilyMemberCard
											key={mother.id}
											relationship="Mother"
											name={mother.name}
											avatar={mother.avatar}
											onDelete={() => openDeleteConfirm(mother.id, mother.name)}
										/>
									))}
									{/* Mother End */}
								</FamilyGroup>
								<div className="flex items-center justify-start gap-8">
									<FamilyGroup title="Paternal Uncles">
									{/* Maternal Uncles Start */}
									{tree.maternalUncles.map((uncle) => (
										<FamilyMemberCard
											key={uncle.id}
											relationship="Uncle"
											name={uncle.name}
											avatar={uncle.avatar}
											onDelete={() => openDeleteConfirm(uncle.id, uncle.name)}
										/>
									))}
									{/* Maternal Uncles End */}
									{/* Maternal Aunts Start */}
									{tree.maternalAunts.map((aunt) => (
										<FamilyMemberCard
											key={aunt.id}
											relationship="Aunt"
											name={aunt.name}
											avatar={aunt.avatar}
											onDelete={() => openDeleteConfirm(aunt.id, aunt.name)}
										/>
									))}
									{/* Maternal Aunts End */}
									</FamilyGroup>
								</div>
							</div>
							<div className="grid w-full grid-cols-[1fr_auto_1fr] items-center gap-8">
								<div className="flex items-center justify-end gap-8">
								{/* Paternal Cousins Start */}
								<FamilyGroup title="Paternal Cousins">
									{tree.paternalCousins.map((cousin) => (
										<FamilyMemberCard
											key={cousin.id}
											relationship="Cousin"
											name={cousin.name}
											avatar={cousin.avatar}
											onDelete={() => openDeleteConfirm(cousin.id, cousin.name)}
										/>
									))}
								</FamilyGroup>
								{/* Paternal Cousins End */}
								{/* Brothers Start */}
								<FamilyGroup title="Brothers">
									{tree.brothers.map((brother) => (
										<FamilyMemberCard
											key={brother.id}
											relationship="Brother"
											name={brother.name}
											avatar={brother.avatar}
											onDelete={() =>
												openDeleteConfirm(brother.id, brother.name)
											}
										/>
									))}
								</FamilyGroup>
								{/* Brothers End */}
								</div>
								{/* Current User Start */}
								<FamilyGroup title="You">
									<FamilyMemberCard
										key={auth?.user?.id}
										relationship="You"
										name={auth?.user?.name ?? ""}
										avatar={auth?.user?.avatar}
										isCurrentUser
									/>
									{/* Current User End */}
									{/* Spouse Start */}
									{/* {tree.spouse.map((spouse) => (
										<FamilyMemberCard
											key={spouse.id}
											relationship="Spouse"
											name={spouse.name}
											avatar={spouse.avatar}
											onDelete={() => openDeleteConfirm(spouse.id, spouse.name)}
										/>
									))} */}
								</FamilyGroup>
								{/* Spouse End */}
								<div className="flex items-center justify-start gap-8">
								{/* Sisters Start */}
								<FamilyGroup title="Sisters">
									{tree.sisters.map((sister) => (
										<FamilyMemberCard
											key={sister.id}
											relationship="Sister"
											name={sister.name}
											avatar={sister.avatar}
											onDelete={() => openDeleteConfirm(sister.id, sister.name)}
										/>
									))}
								</FamilyGroup>
								{/* Sisters End */}
								{/* Maternal Cousins Start */}
								<FamilyGroup title="Maternal Cousins">
									{tree.maternalCousins.map((cousin) => (
										<FamilyMemberCard
											key={cousin.id}
											relationship="Cousin"
											name={cousin.name}
											avatar={cousin.avatar}
											onDelete={() => openDeleteConfirm(cousin.id, cousin.name)}
										/>
									))}
								</FamilyGroup>
								{/* Maternal Cousins End */}
								</div>
							</div>
							{/* Children Start */}
							<div className="flex w-full items-start justify-center gap-8">
								{/* Paternal Nephews Start */}
								<FamilyGroup title="Paternal Nephews">
									{tree.paternalNephews.map((nephew) => (
										<FamilyMemberCard
											key={nephew.id}
											relationship="Nephew"
											name={nephew.name}
											avatar={nephew.avatar}
											onDelete={() => openDeleteConfirm(nephew.id, nephew.name)}
										/>
									))}
								</FamilyGroup>
								{/* Paternal Nephews End */}
								{/* Paternal Nieces Start */}
								<FamilyGroup title="Paternal Nieces">
									{tree.paternalNieces.map((niece) => (
										<FamilyMemberCard
											key={niece.id}
											relationship="Niece"
											name={niece.name}
											avatar={niece.avatar}
											onDelete={() => openDeleteConfirm(niece.id, niece.name)}
										/>
									))}
								</FamilyGroup>
								{/* Paternal Nieces End */}
								{/* Sons Start */}
								<FamilyGroup title="Sons">
									{tree.sons.map((son) => (
										<FamilyMemberCard
											key={son.id}
											relationship="Son"
											name={son.name}
											avatar={son.avatar}
											onDelete={() => openDeleteConfirm(son.id, son.name)}
										/>
									))}
								</FamilyGroup>
								{/* Sons End */}
								{/* Daughters Start */}
								<FamilyGroup title="Daughters">
									{tree.daughters.map((daughter) => (
										<FamilyMemberCard
											key={daughter.id}
											relationship="Daughter"
											name={daughter.name}
											avatar={daughter.avatar}
											onDelete={() =>
												openDeleteConfirm(daughter.id, daughter.name)
											}
										/>
									))}
								</FamilyGroup>
								{/* Daughters End */}
								{/* Maternal Nephews Start */}
								<FamilyGroup title="Maternal Nephews">
									{tree.maternalNephews.map((nephew) => (
										<FamilyMemberCard
											key={nephew.id}
											relationship="Nephew"
											name={nephew.name}
											avatar={nephew.avatar}
											onDelete={() => openDeleteConfirm(nephew.id, nephew.name)}
										/>
									))}
								</FamilyGroup>
								{/* Maternal Nephews End */}
								{/* Maternal Nieces Start */}
								<FamilyGroup title="Maternal Nieces">
									{tree.maternalNieces.map((niece) => (
										<FamilyMemberCard
											key={niece.id}
											relationship="Niece"
											name={niece.name}
											avatar={niece.avatar}
											onDelete={() => openDeleteConfirm(niece.id, niece.name)}
										/>
									))}
								</FamilyGroup>
								{/* Maternal Nieces End */}
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
				onOpenChange={(open) => {
					setIsDeleteConfirmOpen(open)

					if (!open) {
						setDeleteRelationshipId(null)
						setDeleteRelationshipName("")
					}
				}}>
				<DialogContent className="sm:max-w-sm">
					<DialogHeader>
						<DialogTitle>Remove Relationship</DialogTitle>
						<DialogDescription>
							This will remove the relationship between you and{" "}
							<strong>{deleteRelationshipName || "this family member"}</strong>.
							This action cannot be undone.
						</DialogDescription>
					</DialogHeader>
					<div className="flex justify-end gap-2 pt-2">
						<Button
							variant="outline"
							className="cursor-pointer"
							onClick={() => {
								setIsDeleteConfirmOpen(false)
								setDeleteRelationshipId(null)
								setDeleteRelationshipName("")
							}}>
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
