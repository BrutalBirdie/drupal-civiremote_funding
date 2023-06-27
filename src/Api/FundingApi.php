<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Drupal\civiremote_funding\Api;

use Drupal\civiremote_funding\Api\DTO\ApplicationProcessActivity;
use Drupal\civiremote_funding\Api\DTO\FundingCase;
use Drupal\civiremote_funding\Api\DTO\FundingCaseInfo;
use Drupal\civiremote_funding\Api\DTO\FundingCaseType;
use Drupal\civiremote_funding\Api\DTO\FundingProgram;
use Drupal\civiremote_funding\Api\DTO\PayoutProcess;
use Drupal\civiremote_funding\Api\DTO\TransferContract;
use Drupal\civiremote_funding\Api\Form\FormSubmitResponse;
use Drupal\civiremote_funding\Api\Form\FormValidationResponse;
use Drupal\civiremote_funding\Api\Form\FundingForm;

class FundingApi {

  public function __construct(CiviCRMApiClientInterface $apiClient) {
    $this->apiClient = $apiClient;
  }

  public function getFundingCase(string $remoteContactId, int $fundingCaseId): ?FundingCase {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'get', [
      'remoteContactId' => $remoteContactId,
      'where' => [['id', '=', $fundingCaseId]],
    ]);

    return FundingCase::oneOrNullFromApiResult($result);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getFundingCaseInfoByApplicationProcessId(
    string $remoteContactId,
    int $applicationProcessId
  ): ?FundingCaseInfo {
    $result = $this->apiClient->executeV4('RemoteFundingCaseInfo', 'get', [
      'remoteContactId' => $remoteContactId,
      'where' => [['application_process_id', '=', $applicationProcessId]],
    ]);

    return FundingCaseInfo::oneOrNullFromApiResult($result);
  }

  /**
   * @return array<FundingCaseType>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getFundingCaseTypesByFundingProgramId(string $remoteContactId, int $fundingProgramId): array {
    $result = $this->apiClient->executeV4('RemoteFundingCaseType', 'getByFundingProgramId', [
      'remoteContactId' => $remoteContactId,
      'fundingProgramId' => $fundingProgramId,
    ]);

    return FundingCaseType::allFromArrays($result['values']);
  }

  public function getFundingProgram(string $remoteContactId, int $fundingProgramId): ?FundingProgram {
    $result = $this->apiClient->executeV4('RemoteFundingProgram', 'get', [
      'remoteContactId' => $remoteContactId,
      'where' => [['id', '=', $fundingProgramId]],
    ]);

    return FundingProgram::oneOrNullFromApiResult($result);
  }

  /**
   * @phpstan-return array<FundingProgram>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getFundingPrograms(string $remoteContactId): array {
    $result = $this->apiClient->executeV4('RemoteFundingProgram', 'get', [
      'remoteContactId' => $remoteContactId,
    ]);

    return FundingProgram::allFromArrays($result['values']);
  }

  private CiviCRMApiClientInterface $apiClient;

  /**
   * @phpstan-return array<ApplicationProcessActivity>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getApplicationActivities(string $remoteContactId, int $applicationProcessId): array {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcessActivity', 'get', [
      'remoteContactId' => $remoteContactId,
      'applicationProcessId' => $applicationProcessId,
    ]);

    return ApplicationProcessActivity::allFromArrays($result['values']);
  }

  /**
   * @phpstan-return array<string, \Drupal\civiremote_funding\Api\DTO\Option>
   *   Options with option ID as key.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getApplicationStatusOptions(string $remoteContactId, int $applicationProcessId): array {
    return FieldOptionsLoader::new($this->apiClient)->getOptions(
      $remoteContactId,
      'RemoteFundingApplicationProcess',
      'status',
      ['id' => $applicationProcessId],
    );
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getApplicationForm(string $remoteContactId, int $applicationProcessId): FundingForm {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcess', 'getForm', [
      'remoteContactId' => $remoteContactId,
      'applicationProcessId' => $applicationProcessId,
    ]);

    return FundingForm::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function validateApplicationForm(string $remoteContactId, array $data): FormValidationResponse {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcess', 'validateForm', [
      'remoteContactId' => $remoteContactId,
      'data' => $data,
    ]);

    return FormValidationResponse::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function submitApplicationForm(string $remoteContactId, array $data): FormSubmitResponse {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcess', 'submitForm', [
      'remoteContactId' => $remoteContactId,
      'data' => $data,
    ]);

    return FormSubmitResponse::fromApiResultValue($result['values']);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getNewApplicationForm(
    string $remoteContactId,
    int $fundingProgramId,
    int $fundingCaseTypeId
  ): FundingForm {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'getNewApplicationForm', [
      'remoteContactId' => $remoteContactId,
      'fundingProgramId' => $fundingProgramId,
      'fundingCaseTypeId' => $fundingCaseTypeId,
    ]);

    return FundingForm::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function validateNewApplicationForm(string $remoteContactId, array $data): FormValidationResponse {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'validateNewApplicationForm', [
      'remoteContactId' => $remoteContactId,
      'data' => $data,
    ]);

    return FormValidationResponse::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function submitNewApplicationForm(string $remoteContactId, array $data): FormSubmitResponse {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'submitNewApplicationForm', [
      'remoteContactId' => $remoteContactId,
      'data' => $data,
    ]);

    return FormSubmitResponse::fromApiResultValue($result['values']);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getTransferContract(string $remoteContactId, int $fundingCaseId): ?TransferContract {
    $result = $this->apiClient->executeV4('RemoteFundingTransferContract', 'get', [
      'remoteContactId' => $remoteContactId,
      'where' => [['funding_case_id', '=', $fundingCaseId]],
    ]);

    return TransferContract::oneOrNullFromApiResult($result);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getPayoutProcess(string $remoteContactId, int $payoutProcessId): ?PayoutProcess {
    $result = $this->apiClient->executeV4('RemoteFundingPayoutProcess', 'get', [
      'remoteContactId' => $remoteContactId,
      'where' => [['id', '=', $payoutProcessId]],
    ]);

    return PayoutProcess::oneOrNullFromApiResult($result);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function createDrawdown(string $remoteContactId, int $payoutProcessId, float $amount): void {
    $this->apiClient->executeV4('RemoteFundingDrawdown', 'create', [
      'remoteContactId' => $remoteContactId,
      'payoutProcessId' => $payoutProcessId,
      'amount' => $amount,
    ]);
  }

}
